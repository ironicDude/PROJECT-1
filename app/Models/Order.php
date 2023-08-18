<?php

namespace App\Models;

use App\Events\MinimumStockLevelExceeded;
use App\Exceptions\EmptyOrderException;
use App\Exceptions\InShortageException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\ProductAlreadyAddedException;
use App\Exceptions\ProductNotAddedException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Notifications\MinimumStockLevelExceededNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;


class Order extends Model
{
    use HasFactory;
    use SingleTableInheritanceTrait;

    protected $table = 'orders';
    protected static $singleTableTypeField = 'method';
    protected static $singleTableSubclasses = [InStoreOrder::class, OnPhoneOrder::class, OnlineOrder::class];
    protected static $persisted = [
        'id',
        'created_at',
        'updated_at',
        'status',
        'shipping_fees',
        'method',
        'delivery_date',
        'employee_id',
        'customer_id',
        'delivery_fees',
        'shipping_address',
    ];
    protected $fillable = [
        'customer_id',
        'delivery_employee_id',
        'total',
        'shipping_fees',
        'shipping_address',
        'quantity',
        'method',
        'status',
    ];

    public function storeShippingAddress(string $shippingAddress)
    {
        $this->shipping_address = $shippingAddress;
        $this->save();
        return $this->shipping_address;
    }

    public function getShippingAddress()
    {
        return $this->shipping_address;
    }

    public static function getCustomerOrders(int $customerId, string $date = null, string $status = null)
    {
        $user = User::findOrFail($customerId);
        $orders = $user->orders();
        if ($date) {
            $orders = $orders->whereDate('updated_at', $date);
        }
        if($status) {
            $orders = $orders->whereStatus($status);
        }
        return $orders;
    }

    public static function getAllOrders(string $date = null, string $status = null)
    {
        $orders = self::query();
        if ($date) {
            $orders = $orders->whereDate('updated_at', $date);
        }
        if($status) {
            $orders = $orders->whereStatus($status);
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
        foreach ($filePaths as $path) {
            $fileContents = file_get_contents("C:\Programming\Laravel\PROJECT-1\storage\app\\{$path}");
            $encodedContents = base64_encode($fileContents);
            if (pathinfo($path, PATHINFO_EXTENSION) === 'pdf') {
                $data[] = mb_convert_encoding("data:application/pdf;base64,{$encodedContents}", 'UTF-8');
            } else {
                $imgExtension = pathinfo($path, PATHINFO_EXTENSION);
                $data[] = mb_convert_encoding("data:image/{$imgExtension};base64,{$encodedContents}", 'UTF-8');
            }
        }
        return $data;
    }

    public static function calculateRevenue(int $days)
    {
        $date = Carbon::now()->subDays($days);
        $orders = self::where('updated_at', '>=', $date)->get();
        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getTotal();
        }
        return $total;
    }

    public static function countOrders(int $days)
    {
        $date = Carbon::now()->subDays($days);
        $count = self::all()->where('updated_at', '>=', $date)->count();
        return $count;
    }


    public static function chartOrders(string $date, string $period)
    {
        $points = collect();
        $counter = 0;
        switch ($period) {
            case 'day':
                for ($i = 23; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subHours($i);
                    $end = Carbon::parse($date)->subHours($i - 1);
                    $ordersMade = self::where('updated_at', '>=', $start)
                        ->where('updated_at', '<', $end)
                        ->count();
                    $points->push([
                        'hour' => $counter++,
                        'ordersMade' => $ordersMade,
                    ]);
                }
                break;
            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $ordersMade = self::where('updated_at', '>=', $start)
                        ->where('updated_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $counter++,
                        'ordersMade' => $ordersMade,
                    ]);
                }
                break;
            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $ordersMade = self::where('updated_at', '>=', $start)
                        ->where('updated_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $counter++,
                        'ordersMade' => $ordersMade,
                    ]);
                }
                break;
            case 'year':
                for ($i = 364; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $ordersMade = self::where('updated_at', '>=', $start)
                        ->where('updated_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $counter++,
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
        $counter = 0;
        switch ($period) {
            case 'day':
                for ($i = 23; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subHours($i);
                    $end = Carbon::parse($date)->subHours($i - 1);
                    $ordersMade = self::where('updated_at', '>=', $start)
                        ->where('updated_at', '<', $end)->get();
                    $revenue = 0;
                    foreach ($ordersMade as $order) {
                        $revenue += $order->getTotal();
                    }
                    $data = collect([
                        'hour' => $counter++,
                        'revenue' => $revenue,
                    ]);
                    $points->push($data);
                }
                break;
            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $ordersMade = self::where('updated_at', '>=', $start)
                        ->where('updated_at', '<', $end)->get();
                    $revenue = 0;
                    foreach ($ordersMade as $order) {
                        $revenue += $order->getTotal();
                    }
                    $data = collect([
                        'day' => $counter++,
                        'revenue' => $revenue,
                    ]);
                    $points->push($data);
                }
                break;
            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $ordersMade = self::where('updated_at', '>=', $start)
                        ->where('updated_at', '<', $end)->get();
                    $revenue = 0;
                    foreach ($ordersMade as $order) {
                        $revenue += $order->getTotal();
                    }
                    $data = collect([
                        'day' => $counter++,
                        'revenue' => $revenue,
                    ]);
                    $points->push($data);
                }
                break;
            case 'year':
                for ($i = 364; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $ordersMade = self::where('updated_at', '>=', $start)
                        ->where('updated_at', '<', $end)->get();
                    $revenue = 0;
                    foreach ($ordersMade as $order) {
                        $revenue += $order->getTotal();
                    }
                    $data = collect([
                        'day' => $counter++,
                        'revenue' => $revenue,
                    ]);
                    $points->push($data);
                }
                break;
        }

        return $points;
    }

    public static function chartProfit(int $year)
    {
        $points = collect();
        for ($i = 1; $i < 13; $i++) {
            $ordersMadePerMonth = self::whereYear('updated_at', $year)->whereMonth('updated_at', $i)->get();
            $spendings = (int) Payment::whereYear('updated_at', $year)->whereMonth('updated_at', $i)->sum('amount');
            $revenue = 0;
            foreach ($ordersMadePerMonth as $order) {
                $revenue += $order->getTotal();
            }
            $profit = $revenue - $spendings;
            $data = collect([
                'month' => ($i - 1),
                'profit' => $profit,
            ]);
            $points->push($data);
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
        return $product;
    }

    protected function validateStock(PurchasedProduct $product, int $quantity)
    {
        if (!$product->isAvailable()) {
            throw new OutOfStockException();
        }

        if ($quantity > $product->getOrderLimit()) {
            throw new QuantityExceededOrderLimitException();
        }

        DB::commit();
        if (!$product->isMinimumStockLevelSafe() && $quantity > 1) {
            $allowedQuantity = 1;
            $this->updateQuantityAndThrowShortageException($product, $allowedQuantity);
        }

        if ($product->isMinimumStockLevelSafe() && $quantity > $product->getSafeDistance()) {
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
        if ($this->getPurchasedProductItems($product)->count() == 0)
            throw new ProductNotAddedException();
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
        if ($this->orderedProducts()->count() == 0) {
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
                if ($purchasedProduct->getQuantity() < $purchasedProduct->getMinimumStockLevel()) {
                    $inventoryManager = Employee::whereRelation('roles', 'role', 'inventory manager')->first();
                    $admin = Employee::whereRelation('roles', 'role', 'administrator')->first();
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
        foreach ($prescriptions as $prescription) {
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

    protected function markAsUpdated()
    {
        $this->is_updated = 1;
        $this->save();
    }

    protected function markAsUnUpdated()
    {
        $this->is_updated = 1;
        $this->save();
    }

    public function isUpdated()
    {
        return $this->is_updated == 1;
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
