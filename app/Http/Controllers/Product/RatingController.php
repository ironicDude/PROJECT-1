<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductOverviewResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomResponse;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    use CustomResponse;
    public function rate(Product $product, Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'rating' => 'required|integer|min:1|max:5'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $product = new ProductOverviewResource($product->rate($request->rating));
        return self::customResponse('Product rated', $product, 200);
    }

    public function getRating(Product $product)
    {
        $rating = $product->getRating();
        if(!$rating){
            return self::customResponse('This product has not been rated yet', null, 200);
        }
        return self::customResponse('Rating returned', $rating, 200);
    }

    public function getUserRatingToProduct(Product $product)
    {
        $rating = $product->getUserRatingToProduct(Auth::user()->id);
        if(!$rating){
            return self::customResponse('User has not rated this product yet', null, 200);
        }
        return self::customResponse('Rating returned', $rating, 200);
    }
}
