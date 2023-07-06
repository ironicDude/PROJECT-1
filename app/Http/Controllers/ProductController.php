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

    public function barSearch(string $string) {

        $products = Product::select('name as produt_name', 'id as product_id')->where('name', 'like', '%'.$string.'%')->limit(2)->get();

        $drugs = Drug::select('name as drug_name', 'id as drug_id')->where('name', 'like', '%'.$string.'%')->limit(1)->get();

        $data = [$products, $drugs];

        if(sizeof($data)==0){
            return $this->customResponse('no matches to return', null, 204);
        }
        else{
            return $this->customResponse('matches returned', $data, 200);
        }
    }//end of barSearch

    public function index(int $id)
    {
        
    }

}
