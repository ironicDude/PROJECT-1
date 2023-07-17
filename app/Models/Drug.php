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
use Illuminate\Support\Facades\DB;

class Drug extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description',
    'state', 'indication', 'pharmacodynamics',
    'toxicity', 'half_life', 'route_of_elimination',
    'clearance', 'attribute'];



    public static function search($string, $limit=1)
    {
        $drug = DB::select("SELECT name, MAX(id) as drug_id
                                FROM drugs
                                WHERE name LIKE '%{$string}%'
                                GROUP BY name
                                LIMIT {$limit}");
        return [$drug];
    }

    public static function searchCategories(string $string, int $limit=3)
    {
        $categories = DB::select("SELECT DISTINCT name
                                FROM categories
                                WHERE name LIKE '%{$string}%'
                                ORDER BY CHAR_LENGTH(name)
                                LIMIT {$limit}");
        return $categories;
    }// end of searchCategories

    /**
     * Relationships
     */
    public function affectedOrganisms()
    {
        return $this -> hasMany(AffectedOrganism::class, 'drug_id', 'id');
    }
    public function categories()
    {
        return $this -> belongsToMany(Category::class, 'drug_category');
    }
    public function dosages()
    {
        return $this -> hasMany(Dosage::class, 'drug_id', 'id');
    }
    public function externalIdentifiers()
    {
        return $this -> hasMany(ExternalIdentifier::class, 'drug_id', 'id');
    }
    public function interactingDrugs()
    {
        return $this -> belongsToMany(InteractingDrug::class, 'interactions')->withPivot('description');
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
