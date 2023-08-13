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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
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
            $orders = $orders->where('created_at', $date);
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
        $counter = 0;
        switch ($period) {
            case 'day':
                for ($i = 23; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subHours($i);
                    $end = Carbon::parse($date)->subHours($i - 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $points->push([
                        'hour' => $counter,
                        'ordersMade' => $ordersMade,
                    ]);
                    $counter++;
                }
                break;
            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $counter,
                        'ordersMade' => $ordersMade,
                    ]);
                    $counter++;
                }
                break;
            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $counter,
                        'ordersMade' => $ordersMade,
                    ]);
                    $counter++;
                }
                break;
            case 'year':
                for ($i = 364; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $ordersMade = Order::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $counter,
                        'ordersMade' => $ordersMade,
                    ]);
                    $counter++;
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
