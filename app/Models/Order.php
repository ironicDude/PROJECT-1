<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function getTotal()
    {
        return ($this->orderedProducts()->sum('subtotal') + $this->shipping_fees + $this->delivery_fees);
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
        // $fileFullPaths = array_map(function($path){
        //     return "C:\Programming\Laravel\PROJECT-1\storage\app\\{$path}";
        // }, $filePaths);
        return $data;
    }

    public static function calculateRevenueInDays(int $days)
    {
        $date = Carbon::now()->subDays($days);
        $orders = Order::all()->where('created_at', '>=', $date);
        $total = 0;
        foreach($orders as $order){
            $total += $order->getTotal();
        }
        return $total;
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
