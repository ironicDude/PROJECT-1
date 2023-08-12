<?php

namespace App\Models;

use App\Events\MinimumStockLevelExceeded;
use App\Exceptions\InShortageException;
use App\Exceptions\NoPrescriptionsException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\ProductAlreadyAddedException;
use App\Exceptions\ProductNotAddedException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Notifications\MinimumStockLevelExceededNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'total',
        'shipping_fees',
        'shipping_address',
        'quantity',
        'method',
        'status',
    ];

    public static function getCustomerOrders(int $customerId, string $date = null)
    {
        $orders = Order::where('customer_id', $customerId);
        if($date){
            $orders = $orders->whereDate('created_at', $date);
        }
        return $orders;
    }

    public static function getAllOrders(string $date = null)
    {
        $orders = Order::query();
        if($date){
            $orders = $orders->whereDate('created_at', $date);
        }
        return $orders;
    }
    public function getTotal()
    {
        return ($this->orderedProducts()->sum('subtotal') + $this->shipping_fees);
    }

    public function getQuantity()
    {
        return $this->orderedProducts()->sum('quantity');
    }

    public function viewPrescriptions()
    {
        $filePaths = $this->prescriptions->pluck('prescription')->toArray();

        $data = [];
        foreach($filePaths as $path){
            $fileContents = file_get_contents("C:\Programming\Laravel\PROJECT-1\storage\app\\{$path}");
            $encodedContents = base64_encode($fileContents);
            if(pathinfo($path, PATHINFO_EXTENSION) === 'pdf'){
                $data[] = mb_convert_encoding("data:application/pdf;base64,{$encodedContents}", 'UTF-8');
            }
            else{
                $imgExtension = pathinfo($path, PATHINFO_EXTENSION);
                $data[] = mb_convert_encoding("data:image/{$imgExtension};base64,{$encodedContents}", 'UTF-8');
            }
        }
        return $data;
    }

    public static function calculateRevenue(int $days)
    {
        $date = Carbon::now()->subDays($days);
        $orders = Order::where('created_at', '>=', $date)->get();
        $total = 0;
        foreach($orders as $order){
            $total += $order->getTotal();
        }
        return $total;
    }

    public static function countOrders(int $days)
    {
        $date = Carbon::now()->subDays($days);
        $count = Order::all()->where('created_at', '>=', $date)->count();
        return $count;
    }


    public static function chartOrders(string $date, string $period)
    {
        $points = collect();
        switch ($period) {
            case 'day':
                for ($i = 0; $i < 24; $i++) {
                    $start = Carbon::parse($date)->addHours($i);
                    $end = Carbon::parse($date)->addHours($i + 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $points->push([
                        'hour' => $i,
                        'ordersMade' => $ordersMade,
                    ]);
                }
                break;
            case 'week':
                for ($i = 0; $i < 7; $i++) {
                    $start = Carbon::parse($date)->addDays($i);
                    $end = Carbon::parse($date)->addDays($i + 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $i,
                        'ordersMade' => $ordersMade,
                    ]);
                }
                break;
            case 'month':
                for ($i = 0; $i < 30; $i++) {
                    $start = Carbon::parse($date)->addDays($i);
                    $end = Carbon::parse($date)->addDays($i + 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $i,
                        'ordersMade' => $ordersMade,
                    ]);
                }
                break;
            case 'year':
                for ($i = 0; $i < 365; $i++) {
                    $start = Carbon::parse($date)->addDays($i);
                    $end = Carbon::parse($date)->addDays($i + 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $points->push([
                        'hour' => $i,
                        'ordersMade' => $ordersMade,
                    ]);
                }
                break;
        }

        return $points;
    }

    public static function chartRevenue(string $date, string $period)
    {
        $points = collect();
        switch ($period) {
            case 'day':
                for ($i = 0; $i < 24; $i++) {
                    $start = Carbon::parse($date)->addHours($i);
                    $end = Carbon::parse($date)->addHours($i + 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)->get();
                    $revenue = 0;
                    foreach($ordersMade as $order){
                        $revenue += $order->getTotal();
                    }
                    $points->push([
                        'hour' => $i,
                        'revenue' => $revenue,
                    ]);
                }
                break;
            case 'week':
                for ($i = 0; $i < 7; $i++) {
                    $start = Carbon::parse($date)->addDays($i);
                    $end = Carbon::parse($date)->addDays($i + 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)->get();
                    $revenue = 0;
                    foreach($ordersMade as $order){
                        $revenue += $order->getTotal();
                    }
                    $points->push([
                        'day' => $i,
                        'revenue' => $revenue,
                    ]);
                }
                break;
            case 'month':
                for ($i = 0; $i < 30; $i++) {
                    $start = Carbon::parse($date)->addDays($i);
                    $end = Carbon::parse($date)->addDays($i + 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)->get();
                    $revenue = 0;
                    foreach($ordersMade as $order){
                        $revenue += $order->getTotal();
                    }
                    $points->push([
                        'day' => $i,
                        'revenue' => $revenue,
                    ]);
                }
                break;
            case 'year':
                for ($i = 0; $i < 365; $i++) {
                    $start = Carbon::parse($date)->addDays($i);
                    $end = Carbon::parse($date)->addDays($i + 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)->get();
                    $revenue = 0;
                    foreach($ordersMade as $order){
                        $revenue += $order->getTotal();
                    }
                    $points->push([
                        'day' => $i,
                        'revenue' => $revenue,
                    ]);
                }
                break;
        }

        return $points;
    }



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
                $this->prescriptions->each(function ($prescription) {
                    Storage::disk('local')->delete($prescription->prescription);
                    $prescription->delete();
                });
                $this->delete();
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

            $this->checkPrescriptionProducts();

            $this->processStock();

            $this->processPayment();

        });
    }

    protected function checkPrescriptionProducts()
    {
        $containsPrescriptionProducts = false;

        foreach ($this->orderedProducts as $product) {
            if ($product->datedProduct->purchasedProduct->product->otc == 0) {
                $containsPrescriptionProducts = true;
            }
        }

        if ($this->checkPrescriptionsUpload() == false && $containsPrescriptionProducts) {
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
        if($prescriptions->count() == 0) throw new NoPrescriptionsException('Order has no prescriptions stored');
        foreach($prescriptions as $prescription)
        {
            Storage::disk('local')->delete($prescription->prescription);
            $prescription->delete();
        }
        return $prescriptions->pluck('prescription');
    }

    public function checkIfPrescriptionsAreAdded()
    {
        return $this->prescriptions->count() > 0 ? true : false;
    }

    public function clear()
    {
        DB::transaction(function () {
            $this->orderedProducts->each->delete();

            $prescriptions = $this->prescriptions;

            $prescriptions->each(function ($prescription) {
                Storage::disk('local')->delete($prescription->prescription);
                $prescription->delete();
            });

            $this->delete();
        });
    }
    /**
     * relationships
     */

     public function prescriptions()
     {
        return $this->hasMany(Prescription::class, 'order_id', 'id');
     }

     public function orderedProducts()
     {
        return $this->hasMany(OrderedProduct::class, 'order_id', 'id');
     }

     public function customer()
     {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
     }

     public function employee()
     {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
     }
}
