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


    public function index()
    {
        $products = new ProductCollection(Product::orderBy('id')->cursorPaginate(15));
        if(!$products){
            return $this->customResponse('no products to return', null, 204);
        }
        else{
            return $products;
        }
    }//end of index

    public function search(string $string) {

        $products = Product::search($string);

        if(count($products)==0){
            return $this->customResponse('no matches to return', null, 204);
        }
        else{
            return $this->customResponse('matches returned', $products, 200);
        }
    }//end of barSearch

    public function show(int $id)
    {
        $product = Product::findOrFail($id);
        $isAvailable = 1;

        $data = [$product, 'is_available' => $isAvailable];

        return $this->customResponse('product returned', $data, 200);
    }
}
