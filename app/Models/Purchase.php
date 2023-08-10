<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

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
        return ($this->datedProducts()->sum('subtotal') + $this->shipping_fees);
    }

    public function getQuantity()
    {
        return $this->datedProducts()->sum('quantity');
    }

}
