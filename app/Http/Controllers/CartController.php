<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\ItemNotInCartException;
use App\Exceptions\NullAddressException;
use App\Exceptions\NullQuantityException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Models\CartedProduct;
use App\Models\PurchasedProduct;
use Illuminate\Queue\NullQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ItemNotFoundException;

class CartController extends Controller
{
    use CustomResponse;

    /**
     * Add the given product to the cart.
     *
     * @param Product $product The product to add to the cart.
     * @param Request $request The HTTP request containing additional data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the status of the operation.
     */
    public function store(Product $product, Request $request)
    {
        try {
            // Call the static method to add the product to the cart.
            Cart::addItem($product, $request);
        } catch (QuantityExceededOrderLimitException $e) {
            return self::customResponse("For some regulatory purposes, you cannot order as many of this product", null, 403);
        } catch (OutOfStockException $e) {
            return self::customResponse("Out of stock", null, 403);
        }

        return self::customResponse('Item stored', null, 200);
    }

    /**
     * Remove the given carted product from the cart.
     *
     * @param CartedProduct $cartedProduct The carted product to remove.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the status of the operation.
     */
    public function remove(CartedProduct $cartedProduct)
    {
        // Call the static method to remove the carted product from the cart.
        Cart::removeItem($cartedProduct);

        return self::customResponse('Item removed', null, 200);
    }

    /**
     * Update the quantity of the given carted product in the cart.
     *
     * @param CartedProduct $cartedProduct The carted product to update.
     * @param Request $request The HTTP request containing the updated quantity.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the status of the operation.
     */
    public function updateQuantity(CartedProduct $cartedProduct, Request $request)
    {
        try {
            // Call the static method to update the quantity of the carted product.
            Cart::updateQuantity($cartedProduct, $request);
        } catch (QuantityExceededOrderLimitException $e) {
            return self::customResponse("For some regulatory purposes, you cannot order as many of this product", null, 403);
        } catch (OutOfStockException $e) {
            return self::customResponse("Out of stock", null, 403);
        }

        return self::customResponse('Quantity updated', null, 200);
    }

    /**
     * Store the address for the cart.
     *
     * @param Request $request The HTTP request containing the address data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the status of the operation.
     */
    public function storeAddress(Request $request)
    {
        try {
            // Call the static method to store the address for the cart.
            $addrss = Cart::storeAdress($request);
        } catch (EmptyCartException $e) {
            return self::customResponse('Cart is empty', null, 404);
        }

        return self::customResponse('Address stored', null, 200);
    }

    /**
     * Get the stored address for the cart.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the cart's address.
     */
    public function getAddress()
    {
        try {
            // Call the static method to get the address for the cart.
            $address = Cart::getAddress();
        } catch (EmptyCartException $e) {
            return self::customResponse('Cart is empty', null, 404);
        }

        return self::customResponse('Address returned', $address, 200);
    }

    /**
     * Retrieve the cart information for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the cart information.
     */
    public function show()
    {
        try {
            // Get the cart information for the authenticated user.
            $cart = Auth::user()->cart;
        } catch (EmptyCartException $e) {
            return self::customResponse('Cart is empty', null, 404);
        }

        // Return the cart information as a JSON response.
        return self::customResponse('Cart info returned', new CartResource($cart), 200);
    }

    /**
     * Process the checkout request and complete the purchase.
     *
     * @param Request $request The HTTP request containing the checkout data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the status of the operation.
     */
    public function checkout(Request $request)
    {
        try {
            // Call the static method to process the checkout and complete the purchase.
            Cart::checkout($request);
        } catch (EmptyCartException $e) {
            return self::customResponse('Cart is empty', null, 404);
        } catch (NullAddressException $e) {
            return self::customResponse('Address is required', null, 403);
        } catch (PrescriptionRequiredException $e) {
            return self::customResponse('Prescriptions required', null, 403);
        }

        return self::customResponse('Purchase complete', null, 200);
    }

    /**
     * Get the quantity of items in the cart.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the cart's total quantity.
     */
    public function getQuantity()
    {
        try {
            // Call the static method to get the total quantity of items in the cart.
            $quantity = Cart::getQuantity();
        } catch (EmptyCartException $e) {
            return self::customResponse('Cart is empty', null, 404);
        }

        return self::customResponse('Quantity returned', $quantity, 200);
    }

    /**
     * Get the total amount of the cart.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the cart's total amount.
     */
    public function getTotal()
    {
        try {
            // Call the static method to get the total amount of the cart.
            $total = Cart::getTotal();
        } catch (EmptyCartException $e) {
            return self::customResponse('Cart is empty', null, 404);
        }

        return self::customResponse('Total returned', $total, 200);
    }

    /**
     * Clear the cart, removing all carted products and associated prescriptions.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the status of the operation.
     */
    public function clear()
    {
        try {
            // Call the static method to clear the cart, removing all carted products and prescriptions.
            Cart::clear();
        } catch (EmptyCartException $e) {
            return self::customResponse('Cart is empty', null, 404);
        }

        return self::customResponse('Cart cleared', null, 200);
    }

    /**
     * Store prescriptions in the cart.
     *
     * @param Request $request The HTTP request containing the prescription files.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the status of the operation.
     */
    public function storePrescriptions(Request $request)
    {
        try {
            // Call the static method to store prescriptions in the cart.
            Cart::storePrescriptions($request);
        } catch (EmptyCartException $e) {
            return self::customResponse('Cart is empty', null, 404);
        }

        return self::customResponse('Prescriptions stored', null, 200);
    }

    /**
     * Check if prescriptions are uploaded in the cart.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the status of the prescriptions upload.
     */
    public function checkPrescriptionsUpload()
    {
        try {
            // Call the static method to check if prescriptions are uploaded in the cart.
            $status = Cart::checkPrescriptionsUpload();
        } catch (EmptyCartException $e) {
            return self::customResponse('Cart is empty', null, 404);
        }

        return self::customResponse('Status returned', $status, 200);
    }
}
