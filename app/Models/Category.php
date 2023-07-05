<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Drug;

class Category extends Model
{
    use HasFactory;

    /**
     * Relationships
     */
    public function drugs()
    {
        return $this -> belongsToMany(Drug::class, 'drug_id', 'id');
    }
    
}
