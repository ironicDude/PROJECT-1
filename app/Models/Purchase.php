<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function getAllPurchases(string $date = null)
    {
        $purchases = Purchase::query();
        if($date){
            $purchases = $purchases->whereDate('created_at', $date);
        }
        return $purchases;
    }
    public function getTotal()
    {
        return ($this->datedProducts()->sum('purchase_price') + $this->shipping_fees);
    }

    public function getQuantity()
    {
        return $this->datedProducts()->sum('quantity');
    }



    /**
     * relationships
     */

    public function datedProducts()
    {
        return $this->hasMany(DatedProduct::class, 'purchase_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

}
