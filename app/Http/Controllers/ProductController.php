<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomResponse;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Route;

class ProductController extends Controller
{
    use CustomResponse;
    public function index()
    {
        $products = ProductResource::collection(Product::all());
        if(!$products){
            return $this->customResponse('no products to return', null, 204);
        }
        else{
            return $this->customResponse('products returned', $products, 200);
        }
    }

}
