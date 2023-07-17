<?php

namespace App\Models;

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

    public static function searchLabellers(Request $request, int $limit=3)
    {
        $string = $request->string;
        $labellers = DB::select("SELECT DISTINCT labeller
                                FROM products
                                WHERE labeller LIKE '%{$string}%'
                                ORDER BY CHAR_LENGTH(labeller)
                                LIMIT {$limit}");
        return $labellers;
    }//end of searchLabellers

    public static function searchRoutes(Request $request, int $limit=3)
    {
        $string = $request->string;
        $routes = DB::select("SELECT DISTINCT name
                                FROM routes
                                WHERE name LIKE '%{$string}%'
                                ORDER BY CHAR_LENGTH(name)
                                LIMIT {$limit}");
        return $routes;
    }// end of searchRoutes

    public static function searchDosageForms(Request $request, int $limit=3)
    {
        $string = $request->string;
        $dosageForms = DB::select("SELECT DISTINCT name
                                FROM dosage_forms
                                WHERE name LIKE '%{$string}%'
                                ORDER BY CHAR_LENGTH(name)
                                LIMIT {$limit}");
        return $dosageForms;
    }// end of searchRoutes

    public static function searchNames(Request $request, int $limit=5)
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
    }//end of search

    public static function index(Request $request)
    {
        $name = $request->name;
        $minPrice = $request->minPrice;
        $maxPrice = $request->maxPrice;
        $category = $request->category;
        $labeller = $request->labeller;
        $route = $request->route;
        $rating = $request->rating;
        $dosageForm = $request->dosage_form;
        $otc = $request->otc;

        $products = Product::query();
        if(strlen($name)!=0){
            $procuts = $products->where('name', 'like', "%{$name}%");
        }
        if(strlen($otc)!=0){
            $products = $products->where('otc', $otc);
        }
        if(strlen($labeller)!=0){
            $products = $products->where('labeller', $labeller);
        }
        if(strlen($dosageForm)!=0){
            $products = $products->whereHas('dosageForms', function($query) use ($dosageForm){
                $query->where('dosage_forms.name', $dosageForm);
                });
        }
        if(strlen($route)!=0){
            $products = $products->whereHas('routes', function($query) use ($route){
                $query->where('routes.name', $route);
                });
        }
        if(strlen($category)!=0){
            $products = $products->whereHas('drug', function($query) use ($category){
                $query->whereHas('categories', function($query) use ($category){
                    $query->where('categories.name', $category);
                });
            });
        }
        if(strlen($rating)!=0){
            $products = $products->whereHas('ratings', function($query) use ($rating){
                $query->selectRaw('AVG(rating) as avg_rating')
                    ->havingRaw('avg_rating >= ?', [$rating]);
            });
        }
        if(strlen($minPrice)!=0){
            $products = $products->whereHas('purchasedProducts', function($query) use ($minPrice){
                $query->where('price', '>=', $minPrice);
            });
        }
        if(strlen($maxPrice)!=0){
            $products = $products->whereHas('purchasedProducts', function($query) use ($maxPrice){
                $query->where('price', '<=', $maxPrice);
            });
        }
        return $products->paginate(15);
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
}
