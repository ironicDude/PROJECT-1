<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Drug;
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'labeller',
        'dosage_form',
        'strength',
        'strength',
        'route',
        'generic',
        'otc',
        'drug_id',
    ];

    /**
     * Relationships
     */
    public function drug()
    {
        return $this -> belongsTo(Drug::class, 'drug_id', 'id');
    }
    
}
