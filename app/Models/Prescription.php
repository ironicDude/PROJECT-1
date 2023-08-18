<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = ['file_name','order_id', 'prescription'];

    /**
     * Relationships
     */

     public function order()
     {
        return $this->belongsTo(Order::class);
     }
}
