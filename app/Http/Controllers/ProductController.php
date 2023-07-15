<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\DrugResource;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Drug;
use App\Models\DrugCategory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use CustomResponse;


    public function index(Request $request)
    {
        $products = new ProductCollection(Product::orderBy('id')->paginate(15));
        if(!$products){
            return $this->customResponse('no products to return', null, 404);
        }
        else{
            return $products;
        }
    }//end of index

    public function search(Request $request) {

        $string = $request->string;
        $products = Product::search($string);

        if(count($products)==0){
            return $this->customResponse('no matches to return', null, 404);
        }
        else{
            return $this->customResponse('matches returned', $products, 200);
        }
    }//end of barSearch

    public function show(Request $request)
    {
        $id = $request->id;
        $product = Product::findOrFail($id);
        $isAvailable = 1;

        $data = [$product, 'is_available' => $isAvailable];

        return $this->customResponse('product returned', $data, 200);
    }//end of show

    public function searchLabellers(Request $request)
    {
        $string = $request->string;
        $labellers = Product::searchLabellers($string);
        if(count($labellers)==0){
            return $this->customResponse('no matches to return', null, 404);
        }
        else{
            return $this->customResponse('matches returned', $labellers, 200);
        }
    }//end of searchLabellers

    public function getRoutes()
    {
        $routes = Product::getRoutes();
        if(count($routes)==0){
            return $this->customResponse('no matches to return', null, 404);
        }
        else{
            return $this->customResponse('matches returned', $routes, 200);
        }
    }//end of getRoutes
}
