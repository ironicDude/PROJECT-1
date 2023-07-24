<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\ItemNotInCartException;
use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\NullAddressException;
use App\Exceptions\NullQuantityException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Http\Resources\CartedProductResource;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Models\CartedProduct;
use App\Models\PurchasedProduct;
use App\Models\User;
use Illuminate\Queue\NullQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ItemNotFoundException;

class CartController extends Controller
{
    use CustomResponse;

    /**
     * Add a product to the cart.
     *
     * @param Product $product The product to be added to the cart.
     * @param Request $request The HTTP request containing the product details.
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the add operation.
     */
    public function store(Product $product, Request $request)
    {
        $this->authorize('storeInCart', $product);
        try {
            $item = Cart::addItem($product, $request);
        } catch (QuantityExceededOrderLimitException $e) {
            return self::customResponse('For some regulatory purposes, you cannot order as many of this product', null, 422);
        } catch (OutOfStockException $e) {
            return self::customResponse('Out of stock', null, 422);
        }
        return self::customResponse('Item stored', $item, 200);
    }

    /**
     * Remove a product from the cart.
     *
     * @param Cart $cart the cart to remove the product from
     * @param CartedProduct $cartedProduct the product to be removed
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the remove operation.
     */
    public function remove(Cart $cart, CartedProduct $cartedProduct)
    {
        $this->authorize('removeFromCart', $cartedProduct);
        $item = $cart->removeItem($cartedProduct);
        return self::customResponse('Item removed', $item, 200);
    }

    /**
     * Update the quantity of a carted product in the cart.
     *
     * @param Request       $request        The HTTP request containing the new quantity.
     * @param Cart          $cart           The cart containing the carted product.
     * @param CartedProduct $cartedProduct  The carted product to update.
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the update operation.
     */
    public function updateQuantity(Request $request, Cart $cart, CartedProduct $cartedProduct)
    {
        $this->authorize('updateQuantity', $cartedProduct);
        try {
            $quantity = $cart->updateQuantity($cartedProduct, $request);
        } catch (QuantityExceededOrderLimitException $e) {
            return self::customResponse('For some regulatory purposes, you cannot order as many of this product', null, 422);
        } catch (OutOfStockException $e) {
            return self::customResponse('Out of stock', null, 422);
        }
        return self::customResponse('Quantity updated', $quantity, 200);
    }

    /**
     * Store the shipping address in the cart.
     *
     * @param Request $request The HTTP request containing the shipping address.
     * @param Cart    $cart    The cart to store the address.
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the address storage operation.
     */
    public function storeAddress(Request $request, Cart $cart)
    {
        $this->authorize('storeAddress', $cart);
        $address = $cart->storeAdress($request);
        return self::customResponse('Address stored', $address, 200);
    }

    /**
     * Get the shipping address from the cart.
     *
     * @param Cart $cart The cart to get the shipping address from.
     * @return \Illuminate\Http\JsonResponse The JSON response with the shipping address.
     */
    public function getAddress(Cart $cart)
    {
        $this->authorize('viewAddress', $cart);
        $address = $cart->getAddress();
        return self::customResponse('Address returned', $address, 200);
    }

    /**
     * Show the cart information for the authenticated user.
     *
     * @param Cart $cart The cart instance for the authenticated user.
     * @return \Illuminate\Http\JsonResponse The JSON response with the cart information.
     */

    public function show(Cart $cart)
    {
        $this->authorize('view', $cart);
        $cart = $cart->show();
        return self::customResponse('Cart info returned', new CartResource($cart), 200);
    }

    /**
     * Process the checkout for the cart.
     *
     * @param Request $request The HTTP request containing the checkout details.
     * @param Cart    $cart    The cart instance for the authenticated user.
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the checkout operation.
     */


    public function checkout(Request $request, Cart $cart)
    {
        $this->authorize('checkout', $cart);
        try {
            $cart->checkout($request);
        } catch (NullAddressException $e) {
            return self::customResponse('Please, provide an shipping address', null, 422);
        } catch (NotEnoughMoneyException $e) {
            return self::customResponse('You bank account does not have enough credit to complete the transaction', null, 422);
        } catch (PrescriptionRequiredException $e) {
            return self::customResponse('You order contains prescription drugs. Please, add the prescription for each product to continue', null, 422);
        }
        return self::customResponse('Purchase complete', null, 200);
    }

    /**
     * Get the total quantity of carted products in the cart.
     *
     * @param Cart $cart The cart instance for the authenticated user.
     * @return \Illuminate\Http\JsonResponse The JSON response with the total quantity of carted products.
     */

    public function getQuantity(Cart $cart)
    {
        $this->authorize('viewQuantity', $cart);
        $quantity = $cart->getQuantity();
        return self::customResponse('Quantity returned', $quantity, 200);
    }

    /**
     * Get the total value of the cart.
     *
     * @param Cart $cart The cart instance for the authenticated user.
     * @return \Illuminate\Http\JsonResponse The JSON response with the total value of the cart.
     */

    public function getTotal(Cart $cart)
    {
        $this->authorize('viewTotal', $cart);
        $total = $cart->getTotal();
        return self::customResponse('Total returned', $total, 200);
    }

    /**
     * Clear the cart by removing all items from it.
     *
     * @param Cart $cart The cart instance for the authenticated user.
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the clear operation.
     */

    public function clear(Cart $cart)
    {
        $this->authorize('clear', $cart);
        $cart->clear();
        return self::customResponse('Cart cleared', null, 200);
    }

    /**
     * Store prescriptions uploaded by the user in the cart.
     *
     * @param Request $request The HTTP request containing the prescription files.
     * @param Cart    $cart    The cart instance for the authenticated user.
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the prescription storage operation.
     */
    public function storePrescriptions(Request $request, Cart $cart)
    {
        $this->authorize('storePrescriptions', $cart);
        $prescriptionNames = $cart->storePrescriptions($request);
        return self::customResponse('Prescriptions stored', $prescriptionNames, 200);
    }

    /**
     * Check if any prescriptions are uploaded
     *
     * @param Cart $cart The cart instance for the authenticated user.
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the clear operation.
     */
    public function checkPrescriptionsUpload(Cart $cart)
    {
        $this->authorize('checkPrescriptionsUpload', $cart);
        $status = $cart->checkPrescriptionsUpload();
        return self::customResponse('Status returned', $status, 200);
    }
}
