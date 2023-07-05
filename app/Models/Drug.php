<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AffectedOrganism;
use App\Models\Category;
use App\Models\Dosage;
use App\Models\ExternalIdentifier;
use App\Models\Interaction;
use App\Models\Price;
use App\Models\Product;
use App\Models\Synonym;
class Drug extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description',
    'state', 'indication', 'pharmacodynamics',
    'toxicity', 'half_life', 'route_of_elimination',
    'clearance', 'attribute'];


    /**
     * Relationships
     */
    public function affectedOrganisms()
    {
        return $this -> hasMany(AffectedOrganism::class, 'drug_id', 'id');
    }
    public function categories()
    {
        return $this -> belongsToMany(Category::class, 'drug_id', 'id');
    }
    public function dosages()
    {
        return $this -> hasMany(Dosage::class, 'drug_id', 'id');
    }
    public function externalIdentifiers()
    {
        return $this -> hasMany(ExternalIdentifier::class, 'drug_id', 'id');
    }
    public function interactions()
    {
        return $this -> hasMany(interaction::class, 'drug_id', 'id');
    }
    public function prices()
    {
        return $this -> hasMany(Price::class, 'drug_id', 'id');
    }
    public function products()
    {
        return $this -> hasMany(Product::class, 'drug_id', 'id');
    }
    public function synonyms()
    {
        return $this -> hasMany(Synonym::class, 'drug_id', 'id');
    }
}
