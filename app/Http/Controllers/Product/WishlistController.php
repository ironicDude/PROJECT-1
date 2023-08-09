<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CustomResponse;
class WishlistController extends Controller
{
    use CustomResponse;
    public function toggleWishlistProduct(Product $product)
    {
        $user = Auth::user();

        // Toggle the allergy status of the product for the authenticated user.
        $user->wishlistedProducts()->toggle($product);

        return self::customResponse('Product toggled in wishlist', null, 200);
    }


    public function checkIfWishlisted(Product $product)
    {
        $user = Auth::user();
        if ($user->wishlistedProduct($product)) {
            return self::customResponse('product is wishlisted', true, 200);
        } else {
            return self::customResponse('product is not wishlisted', false, 200);
        }
    }

    public function getWishlist()
    {
        $user = Auth::user();

        $products = $user->getWishlistedProducts();

        if (!$products) {
            return self::customResponse('no products in the wishlist', null, 404);
        }

        return self::customResponse('products in the wishlist returned', $products, 200);
    }
}
