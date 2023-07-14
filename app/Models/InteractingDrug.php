<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteractingDrug extends Model
{
    use HasFactory;





    /**
     * Relationships
     */

     public function interactingDrugs()
     {
        return $this->belongsToMany(Drug::class, 'interactions')->withPivot('description');
     }
}
