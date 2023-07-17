<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffectedOrganism extends Model
{
    use HasFactory;

    /**
     * Relationships
     */
    public function drug()
    {
        return $this -> belongsTo(Drug::class, 'drug_id', 'id');
    }
}
