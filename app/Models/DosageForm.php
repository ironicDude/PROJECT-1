<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class DosageForm extends Model
{
    use HasFactory;






    /**
     * Relationships
     */

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
