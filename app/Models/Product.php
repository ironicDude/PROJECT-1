<?php

namespace App\Models;

use App\Exceptions\NameNotFoundException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\SuggestionException;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductOverviewCollection;
use App\Http\Resources\ProductOverviewResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Drug;
use Illuminate\Support\Facades\DB;
use App\Models\Route;
use App\Models\DosageForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
     * Search for labellers by name.
     *
     * This static method is responsible for searching labellers by their names based on the provided search string from the request.
     * It performs a SQL query using Laravel's DB class to select distinct labeller names whose names match the search string
     * (using the LIKE operator with a wildcard '%'). The results are ordered by the length of labeller names.
     * The method accepts an optional limit parameter, which determines the maximum number of search results to return (default is 3).
     * The search results are returned as an array of labeller names.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search string.
     * @param int $limit The optional limit for the maximum number of search results (default is 3).
     * @return array An array of labeller names matching the search string.
     */
    public static function searchLabellers(string $string, int $limit = 3)
    {
        $labellers = DB::select("SELECT DISTINCT labeller
                            FROM products
                            WHERE labeller LIKE '%{$string}%'
                            ORDER BY CHAR_LENGTH(labeller)
                            LIMIT {$limit}");
        return $labellers;
    }

    /**
     * Search for routes by name.
     *
     * This static method is responsible for searching routes by their names based on the provided search string from the request.
     * It performs a SQL query using Laravel's DB class to select distinct route names whose names match the search string
     * (using the LIKE operator with a wildcard '%'). The results are ordered by the length of route names.
     * The method accepts an optional limit parameter, which determines the maximum number of search results to return (default is 3).
     * The search results are returned as an array of route names.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search string.
     * @param int $limit The optional limit for the maximum number of search results (default is 3).
     * @return array An array of route names matching the search string.
     */
    public static function searchRoutes(string $string, int $limit = 3)
    {
        $routes = DB::select("SELECT DISTINCT name
                            FROM routes
                            WHERE name LIKE '%{$string}%'
                            ORDER BY CHAR_LENGTH(name)
                            LIMIT {$limit}");
        return $routes;
    }

    /**
     * Search for dosage forms by name.
     *
     * This static method is responsible for searching dosage forms by their names based on the provided search string from the request.
     * It performs a SQL query using Laravel's DB class to select distinct dosage form names whose names match the search string
     * (using the LIKE operator with a wildcard '%'). The results are ordered by the length of dosage form names.
     * The method accepts an optional limit parameter, which determines the maximum number of search results to return (default is 3).
     * The search results are returned as an array of dosage form names.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search string.
     * @param int $limit The optional limit for the maximum number of search results (default is 3).
     * @return array An array of dosage form names matching the search string.
     */
    public static function searchDosageForms(string $string, int $limit = 3)
    {
        $dosageForms = DB::select("SELECT DISTINCT name
                            FROM dosage_forms
                            WHERE name LIKE '%{$string}%'
                            ORDER BY CHAR_LENGTH(name)
                            LIMIT {$limit}");
        return $dosageForms;
    }

    /**
     * Search for products by name or drug name.
     *
     * This static method is responsible for searching products by their names or the names of their associated drugs based on the provided search string from the request.
     * It performs a SQL query using Laravel's DB class to select the product name, maximum product ID, and maximum drug ID
     * for products whose names or associated drug names match the search string (using the LIKE operator with a wildcard '%').
     * The results are grouped by the concatenated product name and drug name.
     * The method accepts an optional limit parameter, which determines the maximum number of search results to return (default is 5).
     * The search results are returned as an array of product objects, each containing the product name, maximum product ID, and maximum drug ID.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search string.
     * @param int $limit The optional limit for the maximum number of search results (default is 5).
     * @return array An array of product objects, each containing the product name, maximum product ID, and maximum drug ID.
     */
    public static function searchNames(string $string, int $limit = 5)
    {
        $products = DB::select("SELECT CONCAT(p.name,' ', '[', d.name, ']') AS name, MAX(p.id) AS drug_id
                            FROM drugs AS d
                            JOIN products AS p ON d.id = p.drug_id
                            WHERE p.name LIKE '%{$string}%' OR d.name LIKE '%{$string}%'
                            GROUP BY CONCAT(p.name,' ', '[', d.name, ']')
                            ORDER BY CHAR_LENGTH(CONCAT(p.name,' ', '[', d.name, ']'))
                            LIMIT {$limit}");
        return $products;
    }

    /**
     * Retrieve a paginated list of products based on various filter parameters.
     *
     * This static method is responsible for querying the products table based on the provided filter parameters from the HTTP request.
     * The method dynamically applies the filters to the query using the Laravel query builder and the 'where' and 'whereHas' methods.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the filter parameters.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator A paginated result set of products matching the filter criteria.
     */
    public static function index(Request $request)
    {
        // Extract filter parameters from the request.
        $name = $request->name;
        $minPrice = $request->minPrice;
        $maxPrice = $request->maxPrice;
        $category = $request->category;
        $labeller = $request->labeller;
        $route = $request->route;
        $rating = $request->rating;
        $dosageForm = $request->dosage_form;
        $otc = $request->otc;
        $availability = $request->availability;

        // Create a base query for the products.
        $products = Product::query();

        // Apply filters to the query dynamically based on the provided filter parameters.

        if (strlen($name) != 0) {
            $products = $products->where('name', 'like', "%{$name}%");
        }

        if (strlen($otc) != 0) {
            $products = $products->where('otc', $otc);
        }

        if (strlen($labeller) != 0) {
            $products = $products->where('labeller', $labeller);
        }

        if (strlen($dosageForm) != 0) {
            $products = $products->whereHas('dosageForms', function ($query) use ($dosageForm) {
                $query->where('dosage_forms.name', $dosageForm);
            });
        }

        if (strlen($route) != 0) {
            $products = $products->whereHas('routes', function ($query) use ($route) {
                $query->where('routes.name', $route);
            });
        }

        if (strlen($category) != 0) {
            $products = $products->whereHas('drug', function ($query) use ($category) {
                $query->whereHas('categories', function ($query) use ($category) {
                    $query->where('categories.name', $category);
                });
            });
        }

        if (strlen($rating) != 0) {
            $products = $products->whereHas('ratings', function ($query) use ($rating) {
                $query->selectRaw('AVG(rating) as avg_rating')
                    ->havingRaw('avg_rating >= ?', [$rating]);
            });
        }

        if (strlen($minPrice) != 0) {
            $products = $products->whereHas('purchasedProduct', function ($query) use ($minPrice) {
                $query->where('price', '>=', $minPrice);
            });
        }

        if (strlen($maxPrice) != 0) {
            $products = $products->whereHas('purchasedProduct', function ($query) use ($maxPrice) {
                $query->where('price', '<=', $maxPrice);
            });
        }

        if (strlen($availability) != 0) {
            if($availability == 1){
                $products = $products->whereHas('purchasedProduct', function ($query) {
                    $query->whereHas('datedProducts', function($query){
                        $query->where('quantity', '>', 0);
                    });
                });
            }
            elseif($availability == 0){
                $producs = $products->whereDoesntHave('purchasedProduct')->orWhereHas('purchasedProduct', function ($query){
                    $query->whereDoesntHave('datedProducts', function($query){
                        $query->where('quantity', '>', 0);
                    });
                });
            }
        }

        // Check if the search query did not yield any results and a product name was provided.
        if (
            !empty($request->name) && empty($request->minPrice) &&
            empty($request->maxPrice) && empty($request->category) &&
            empty($request->labeller) && empty($request->route) &&
            empty($request->rating) && empty($request->dosageForm) &&
            empty($request->otc) && empty($request->availability) && !$products->exists()
        ) {
            // Attempt to suggest a corrected product name using an external API (rxnav.nlm.nih.gov).
            $name = $request->name;
            $response = Http::get("https://rxnav.nlm.nih.gov/REST/spellingsuggestions.json?name={$name}");

            // If suggestions are found, return them as a response.
            if (empty(($response->json()['suggestionGroup']['suggestionList']))) {
                throw new NameNotFoundException("Your search - {$name} - did not match any records. Suggestions: Make sure all words are spelled correctly. Try different words.Try more general words. Try fewer words.");
            }
            $suggestedName = $response->json()['suggestionGroup']['suggestionList']['suggestion'][0];
            $suggestedNameId = Product::where('name', $suggestedName)->value('id');
            throw new SuggestionException("Did you mean {$suggestedName}?", $suggestedName, $suggestedNameId);
        }

        // Return the paginated result set with a default page size of 15.
        return $products->paginate(15);
    }

    public function isPurchased()
    {
        return $this->purchasedProduct ?? true;
    }



    /**
     * Relationships
     */
    public function drug()
    {
        return $this->belongsTo(Drug::class, 'drug_id', 'id');
    }

    public function dosageForms()
    {
        return $this->belongsToMany(DosageForm::class, 'product_dosage_form');
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class, 'product_route');
    }

    public function ratings()
    {
        return $this->belongsToMany(User::class, 'ratings', 'product_id', 'user_id')->withPivot(['rating', 'reviews']);
    }

    public function purchasedProduct()
    {
        return $this->hasOne(PurchasedProduct::class, 'id', 'id');
    }

    public function allergies()
    {
        return $this->belongsToMany(User::class, 'allergies', 'product_id', 'user_id');
    }
}
