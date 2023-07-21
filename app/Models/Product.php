<?php

namespace App\Models;

use App\Exceptions\OutOfStockException;
use App\Http\Resources\ProductCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Drug;
use Illuminate\Support\Facades\DB;
use App\Models\Route;
use App\Models\DosageForm;
use Illuminate\Http\Request;
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
    public static function searchLabellers(Request $request, int $limit = 3)
    {
        $string = $request->string;
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
    public static function searchRoutes(Request $request, int $limit = 3)
    {
        $string = $request->string;
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
    public static function searchDosageForms(Request $request, int $limit = 3)
    {
        $string = $request->string;
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
    public static function searchNames(Request $request, int $limit = 5)
    {
        $string = $request->string;
        $products = DB::select("SELECT CONCAT(p.name,' ', '[', d.name, ']') AS product_name, MAX(p.id) AS product_id, MAX(d.id) AS drug_id
                            FROM drugs AS d
                            JOIN products AS p ON d.id = p.drug_id
                            WHERE p.name LIKE '%{$string}%' OR d.name LIKE '%{$string}%'
                            GROUP BY product_name
                            ORDER BY CHAR_LENGTH(product_name)
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
            $products = $products->whereHas('purchasedProducts', function ($query) use ($minPrice) {
                $query->where('price', '>=', $minPrice);
            });
        }

        if (strlen($maxPrice) != 0) {
            $products = $products->whereHas('purchasedProducts', function ($query) use ($maxPrice) {
                $query->where('price', '<=', $maxPrice);
            });
        }

        // Return the paginated result set with a default page size of 15.
        return $products->paginate(15);
    }


    public function getEarliestExpiryDateProduct()
    {
        $product = $this->purchasedProducts()->whereNotNull('expiry_date')-> orderBy('expiry_date')->limit(1)->first();
        return $product;
    }

    public function isAvailble(){
        $products = $this->purchasedProducts();
        if($products->count() == 0){
            throw new OutOfStockException();
        }
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

    public function purchasedProducts()
    {
        return $this->hasMany(PurchasedProduct::class);
    }

    public function allergies()
    {
        return $this->belongsToMany(User::class, 'allergies', 'product_id', 'user_id');
    }
}
