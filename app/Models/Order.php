<?php

namespace App\Models;

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
        'quantity'
    ];




    /**
     * relationships
     */

     public function method()
     {
        return $this->belongsTo(Method::class, 'method_id', 'id');
     }

     public function status()
     {
        return $this->belongsTo(OrderStatus::class, 'status_id', 'id');
     }

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
