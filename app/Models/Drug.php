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



    /**
 * Search for drugs by name.
 *
 * This static method is responsible for searching drugs by their names based on the provided search string.
 * It performs a SQL query using Laravel's DB class to select the drug name and the maximum drug ID for each drug
 * whose name matches the search string (using the LIKE operator with a wildcard '%'). The results are grouped by drug name.
 * The method accepts an optional limit parameter, which determines the maximum number of search results to return (default is 1).
 * The search results are returned as an array of drug objects, each containing the drug name and the maximum drug ID.
 *
 * @param string $string The search string for drugs' names.
 * @param int $limit The optional limit for the maximum number of search results (default is 1).
 * @return array An array of drug objects, each containing the drug name and the maximum drug ID.
 */
public static function search($string, $limit = 1)
{
    $drug = DB::select("SELECT name, MAX(id) as drug_id
                        FROM drugs
                        WHERE name LIKE '%{$string}%'
                        GROUP BY name
                        LIMIT {$limit}");

    return [$drug];
}

/**
 * Search for drug categories by name.
 *
 * This static method is responsible for searching drug categories by their names based on the provided search string.
 * It performs a SQL query using Laravel's DB class to select distinct category names whose names match the search string
 * (using the LIKE operator with a wildcard '%'). The results are ordered by the length of category names.
 * The method accepts an optional limit parameter, which determines the maximum number of search results to return (default is 3).
 * The search results are returned as an array of category names.
 *
 * @param string $string The search string for drug categories' names.
 * @param int $limit The optional limit for the maximum number of search results (default is 3).
 * @return array An array of category names matching the search string.
 */
public static function searchCategories(string $string, int $limit = 3)
{
    $categories = DB::select("SELECT DISTINCT name
                              FROM categories
                              WHERE name LIKE '%{$string}%'
                              ORDER BY CHAR_LENGTH(name)
                              LIMIT {$limit}");

    return $categories;
}


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
