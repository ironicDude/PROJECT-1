<?php

namespace App\Models;

use App\Events\MinimumStockLevelExceeded;
use App\Exceptions\EmptyOrderException;
use App\Exceptions\InShortageException;
use App\Exceptions\NoPrescriptionsException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\ProductAlreadyAddedException;
use App\Exceptions\ProductNotAddedException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Notifications\MinimumStockLevelExceededNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;

class InStoreOrder extends Order
{
    use HasFactory;
    use SingleTableInheritanceTrait;
    protected static $singleTableType = 'storely';
    protected static $persisted = [];


    public function addProduct(PurchasedProduct $product, int $quantity)
    {
        DB::transaction(function () use ($product, $quantity) {

            if ($this->getPurchasedProductItems($product)->count() > 0) {
                throw new ProductAlreadyAddedException();
            }

            $this->validateStock($product, $quantity);

            $itemsData = $this->chooseItems($product, $quantity);

            $this->createItems($itemsData);
        });
        return $product;
    }

    protected function validateStock(PurchasedProduct $product, int $quantity)
    {
        if(!$product->isAvailable()){
            throw new OutOfStockException();
        }

        if($quantity > $product->getOrderLimit()){
            throw new QuantityExceededOrderLimitException();
        }

        DB::commit();
        if (!$product->isMinimumStockLevelSafe() && $quantity > 1) {
            $allowedQuantity = 1;
            $this->updateQuantityAndThrowShortageException($product, $allowedQuantity);
        }

        if($product->isMinimumStockLevelSafe() && $quantity > $product->getSafeDistance()){
            $allowedQuantity = $product->getSafeDistance();
            $this->updatequantityAndThrowShortageException($product, $allowedQuantity);
        }
    }

    protected function updateQuantityAndThrowShortageException(PurchasedProduct $product, int $quantity)
    {
        $productName = $product->getName();
        $this->updateQuantity($product, $quantity);
        throw new InShortageException("Unfortunately, {$productName} is in shortage. We modified its quantity in the order to {$quantity}, which is as high as we can offer at the moment. If the customer does not prefer a partial fulfillment, you can press delete to remove the product from the order");
    }

    protected function chooseItems(PurchasedProduct $product, int $quantity)
    {
        $items = [];
        $quantities = [];
        $itemIds = [];
        $tempQuantity = $quantity;
        while ($tempQuantity > 0) {
            $item = $product->datedProducts()->where('quantity', '>', 0)->whereNotIn('id', $itemIds)->whereNotNull('expiry_date')->orderBy('expiry_date')->first();
            $itemIds[] = $item->id;
            if (!$item) {
                throw new OutOfStockException();
            }
            if ($item->quantity >= $tempQuantity) {
                $quantities[] = $tempQuantity;
                $tempQuantity = 0;
            } else {
                $quantities[] = $item->quantity;
                $tempQuantity -= $item->quantity;
            }
            $items[] = $item;
        }
        return [
            'items' => $items,
            'quantities' => $quantities
        ];
    }

    protected function createItems(array $itemsData)
    {
        $items = $itemsData['items'];
        $quantities = $itemsData['quantities'];
        $counter = 0;
        foreach ($items as $item) {
            $subtotal = $item->purchasedProduct->price * $quantities[$counter];
            OrderedProduct::create([
                'order_id' => $this->id,
                'dated_product_id' => $item->id,
                'quantity' => $quantities[$counter],
                'subtotal' => $subtotal,
            ]);
            $counter++;
        }
    }

    public function removeProduct(PurchasedProduct $product)
    {
        if($this->getPurchasedProductItems($product)->count() == 0) throw new ProductNotAddedException();
        DB::transaction(function () use ($product) {
            $this->deletePurchasedProductItems($product);
            $this->load('orderedProducts');
            if ($this->orderedProducts->count() == 0) {
                $this->deletePrescriptions();
            }
        });
        return $product;
    }

    public function updateQuantity(PurchasedProduct $product, int $newQuantity)
    {
        $this->deletePurchasedProductItems($product);
        $this->load('orderedProducts');
        $this->addProduct($product, $newQuantity);
        return $newQuantity;
    }

    protected function getPurchasedProductItems(PurchasedProduct $product)
    {
        $datedProductIds = $product->datedProducts->pluck('id');
        return $this->orderedProducts->whereIn('dated_product_id', $datedProductIds);
    }

    protected function deletePurchasedProductItems(PurchasedProduct $product)
    {
        $datedProductIds = $product->datedProducts->pluck('id');
        $this->orderedProducts->whereIn('dated_product_id', $datedProductIds)->each->delete();
    }


    public function checkout()
    {
        DB::transaction(function () {

            $this->checkForProducts();

            $this->checkForPrescriptionProducts();

            $this->processStock();

            $this->processPayment();

            $this->markAsPaid();
        });
    }

    protected function markAsPaid()
    {
        $this->status = 'Paid';
        $this->save();
    }

    protected function checkForProducts()
    {
        if($this->orderedProducts()->count() == 0){
            throw new EmptyOrderException('Order must contain products');
        }
    }

    protected function checkForPrescriptionProducts()
    {
        $containsPrescriptionProducts = false;

        foreach ($this->orderedProducts as $product) {
            if ($product->datedProduct->purchasedProduct->product->otc == 0) {
                $containsPrescriptionProducts = true;
            }
        }

        if ($this->checkForPrescriptions() == false && $containsPrescriptionProducts) {
            throw new PrescriptionRequiredException();
        }

        return $containsPrescriptionProducts;
    }

    protected function processPayment()
    {
        DB::transaction(function () {
            $total = $this->getTotal();
            $pharmacy = Pharmacy::first();
            $pharmacy->increment('money', $total);
        });
    }

    protected function processStock()
    {
        $orderedProducts = $this->orderedProducts;

        DB::transaction(function () use ($orderedProducts) {
            $purchasedProducts = [];
            foreach ($orderedProducts as $orderedProduct) {
                $purchasedProduct = $orderedProduct->datedProduct->purchasedProduct;
                if (!in_array($purchasedProduct, $purchasedProducts)) {
                    $quantityInOrder = $this->getPurchasedProductItems($purchasedProduct)->sum('quantity');
                    $this->validateStock($purchasedProduct, $quantityInOrder);
                }
                $orderedProduct->datedProduct()->decrement('quantity', $orderedProduct->quantity);
                $purchasedProducts[] = $purchasedProduct;
                if($purchasedProduct->getQuantity() < $purchasedProduct->getMinimumStockLevel()){
                    $inventoryManager = Employee::whereRelation('roles','role', 'inventory manager')->first();
                    $admin = Employee::whereRelation('roles','role', 'administrator')->first();
                    event(new MinimumStockLevelExceeded($purchasedProduct, $inventoryManager, $admin));
                    $admin->notify(new MinimumStockLevelExceededNotification($purchasedProduct));
                }
            }
        });
    }

    public function storePrescriptions($files)
    {

        $fileNames = [];
        foreach ($files as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $noSpaceOriginalName = str_replace(' ', '', $originalName);
            $filename = "{$noSpaceOriginalName}-{$this->id}.{$file->getClientOriginalExtension()}";
            $fileNames[] = $filename;
            Storage::disk('local')->put($filename, File::get($file));
            $prescription = ['prescription' => $filename];
            $this->prescriptions()->create($prescription);
        }

        return $fileNames;
    }

    public function deletePrescriptions()
    {
        $prescriptions = $this->prescriptions;
        foreach($prescriptions as $prescription)
        {
            Storage::disk('local')->delete($prescription->prescription);
            $prescription->delete();
        }
        return $prescriptions->pluck('prescription');
    }

    public function checkForPrescriptions()
    {
        return $this->prescriptions->count() > 0 ? true : false;
    }

    public function clear()
    {
        DB::transaction(function () {
            $this->orderedProducts->each->delete();
            $this->deletePrescriptions();
        });
    }

    public function destroyOrder()
    {
        DB::transaction(function () {
            $this->deleteProducts();
            $this->deletePrescriptions();
            $this->delete();
        });
    }

    protected function deleteProducts()
    {
        $this->orderedProducts->each->delete();
    }
}
