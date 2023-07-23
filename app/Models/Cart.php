<?php

namespace App\Models;

use App\Exceptions\EmptyCartException;
use App\Exceptions\ItemNotInCartException;
use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\NotEnoutMoneyException;
use App\Exceptions\NullAddressException;
use App\Exceptions\NullQuantityException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Exceptions\UnprocessableQuantityException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Http\Resources\CustomResponse;
use App\Mail\OrderUnderReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ItemNotFoundException;

class Cart extends Model
{
    use HasFactory;
    use CustomResponse;

    protected $fillable = [
        'quantity',
        'subtotal'
    ];
    /**
     * Add an item to the customer's cart.
     *
     * @param Product $product The product to be added to the cart.
     * @param Request $request The HTTP request containing the quantity of the product to be added.
     *
     * @throws QuantityExceededOrderLimitException If the requested quantity exceeds the product's order limit.
     * @throws OutOfStockException If the product is out of stock.
     */
    public static function addItem(Product $product, Request $request)
    {
        // Check if the product is available for purchase.
        $product->isAvailble();

        // Get the authenticated customer.
        $customer = $request->user();

        // Get the product with the earliest expiry date for the customer.
        $item = $product->getEarliestExpiryDateProduct();

        // Validate the request data.
        $request->validate([
            'quantity' => 'required|numeric|min:1', // Assuming quantity should be a positive numeric value.
        ]);

        // Extract the quantity from the request.
        $quantity = $request->quantity;

        // Check if the requested quantity exceeds the product's order limit.
        if ($quantity > $item->order_limit) {
            throw new QuantityExceededOrderLimitException();
        }

        // Check if there is enough quantity in stock for the requested quantity.
        if ($item->quantity - $quantity < $item->minimum_stock_level) {
            throw new OutOfStockException();
        }

        // Calculate the subtotal for the carted product.
        $subtotal = $item->price * $quantity;

        // Retrieve or create a cart for the customer.
        $cart = self::firstOrNew();
        $cart->customer_id = $customer->id;
        $cart->save();

        // Add the carted product to the cart.
        CartedProduct::create([
            'customer_id' => $cart->customer_id,
            'purchased_product_id' => $item->id,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ]);

        // Reduce the product quantity in stock.
        $item->decrement('quantity', $quantity);

        // Update the cart's total and quantity of items.
        self::updateTotal($cart);
        self::updateCartQuantity($cart);
    }

    /**
     * Remove an item from the cart.
     *
     * @param CartedProduct $item The carted product to be removed from the cart.
     */
    public static function removeItem(CartedProduct $item)
    {
        // Get the cart to which the carted product belongs.
        $cart = $item->cart;

        // Get the count of carted products in the cart.
        $cartedProductsCount = $cart->cartedProducts->count();

        // Restore the quantity of the removed item back to the product stock.
        $quantity = $item->quantity;
        $item->increment('quantity', $quantity);

        // Delete the carted product from the cart.
        $item->delete();

        // Update the cart's total and quantity of items.
        self::updateTotal($cart);
        self::updateCartQuantity($cart);

        // If there are no carted products left, delete any associated prescriptions and the cart itself.
        if ($cartedProductsCount == 1) {
            $cart->cartedPrescriptions->each(function ($cartedPrescription) {
                Storage::disk('local')->delete($cartedPrescription->prescription);
                $cartedPrescription->delete();
            });
            $cart->delete();
        }
    }

    /**
     * Update the quantity of a carted product in the cart.
     *
     * @param CartedProduct $item The carted product to be updated.
     * @param Request $request The HTTP request containing the new quantity.
     *
     * @throws QuantityExceededOrderLimitException If the requested quantity exceeds the product's order limit.
     * @throws OutOfStockException If the product is out of stock.
     */
    public static function updateQuantity(CartedProduct $item, Request $request)
    {
        // Extract the quantity from the request.
        $quantity = $request->quantity;

        // Validate the request data.
        $request->validate([
            'quantity' => 'required|numeric|min:1', // Assuming quantity should be a positive numeric value.
        ]);

        // Check if the requested quantity exceeds the product's order limit.
        if ($quantity > $item->purchasedProduct->order_limit) {
            throw new QuantityExceededOrderLimitException();
        }

        // Check if there is enough quantity in stock for the requested quantity.
        if ($item->purchasedProduct->quantity - $quantity < $item->minimum_stock_level) {
            throw new OutOfStockException();
        }

        // Update the carted product's quantity and subtotal.
        $item->quantity = $quantity;
        $item->subtotal = $item->quantity * $item->purchasedProduct->price;
        $item->save();

        // Get the cart to which the carted product belongs.
        $cart = $item->cart;

        // Update the cart's total and quantity of items.
        self::updateTotal($cart);
        self::updateCartQuantity($cart);
    }


    /**
     * Update the total amount for the cart based on the carted products' subtotals.
     *
     * @param Cart $cart The cart to update the total for.
     *
     * @return float The updated total amount for the cart.
     */
    private static function updateTotal(Cart $cart)
    {
        // Get the carted products associated with the cart.
        $cartedProducts = $cart->cartedProducts;

        // Calculate the new total by summing up the subtotals of all carted products.
        $total = $cartedProducts->sum('subtotal');

        // Update the cart's total amount.
        $cart->total = $total;
        $cart->save();

        // Return the updated total amount for reference.
        return $total;
    }

    /**
     * Update the quantity of items in the cart based on the quantities of carted products.
     *
     * @param Cart $cart The cart to update the quantity for.
     *
     * @return int The updated quantity of items in the cart.
     */
    private static function updateCartQuantity(Cart $cart)
    {
        // Get the carted products associated with the cart.
        $cartedProducts = $cart->cartedProducts;

        // Calculate the new quantity by summing up the quantities of all carted products.
        $quantity = $cartedProducts->sum('quantity');

        // Update the cart's quantity of items.
        $cart->quantity = $quantity;
        $cart->save();

        // Return the updated quantity of items for reference.
        return $quantity;
    }

    /**
     * Get the total amount for the current customer's cart.
     *
     * @return float The total amount for the cart.
     *
     * @throws EmptyCartException If the cart is empty (not found).
     */
    public static function getTotal()
    {
        // Get the authenticated customer.
        $customer = Auth::user();

        // Get the customer's cart.
        $cart = $customer->cart;

        // Check if the cart exists, and if not, throw an exception.
        if (!$cart) throw new EmptyCartException();

        // Return the total amount for the cart.
        return $cart->total;
    }

    /**
     * Store the address for the current customer's cart.
     *
     * @param Request $request The HTTP request containing the address data.
     *
     * @return string The stored address.
     *
     * @throws EmptyCartException If the cart is empty (not found).
     */
    public static function storeAdress(Request $request)
    {
        // Validate the request data.
        $request->validate([
            'address' => 'required',
        ]);

        // Get the authenticated customer.
        $customer = Auth::user();

        // Get the customer's cart.
        $cart = $customer->cart;

        // Check if the cart exists, and if not, throw an exception.
        if (!$cart) throw new EmptyCartException();

        // Store the provided address in the cart.
        $cart->address = $request->address;
        $cart->save();

        // Return the stored address for reference.
        return $request->address;
    }

    /**
     * Perform the checkout process for the current customer's cart.
     *
     * @param Request $request The HTTP request containing the checkout data.
     *
     * @throws CheckoutValidationException If the checkout data validation fails.
     * @throws EmptyCartException If the cart is empty (not found).
     */
    public static function checkout(Request $request)
    {
        // Validate the checkout data.
        self::validateCheckout($request);

        // Get the authenticated customer.
        $customer = $request->user();

        // Get the customer's cart.
        $cart = $customer->cart;

        // Update the cart's address with the provided address in the checkout request.
        $cart->address = $request->address;

        // Process the payment for the cart.
        self::processPayment($cart);

        // Create an order based on the cart.
        $order = self::createOrder($cart);

        // Clear the cart after successful checkout.
        self::clear($cart);

        // Send an email to the customer about the order being under review.
        Mail::to($customer)->send(new OrderUnderReview($order));
    }


    /**
     * Clear the cart, removing all carted products and associated prescriptions.
     *
     * @throws EmptyCartException If the cart is empty (no carted products found).
     */
    public static function clear()
    {
        // Get the authenticated customer's cart.
        $cart = Auth::user()->cart;

        // Get all carted products associated with the cart.
        $cartedProducts = $cart->cartedProducts;

        // Check if the cart is empty (no carted products found).
        if (count($cartedProducts) == 0) throw new EmptyCartException();

        // Delete each carted product from the cart in a single line.
        $cart->cartedProducts->each->delete();

        // Get all carted prescriptions associated with the cart.
        $cartedPrescriptions = $cart->cartedPrescriptions;

        // Delete each carted prescription file from storage and its associated entry from the cart.
        $cartedPrescriptions->each(function ($cartedPrescription) {
            Storage::disk('local')->delete($cartedPrescription->prescription);
            $cartedPrescription->delete();
        });

        // Delete the cart itself.
        $cart->delete();
    }

    /**
     * Get the total quantity of items in the cart.
     *
     * @return int The total quantity of items in the cart.
     *
     * @throws EmptyCartException If the cart is empty (not found).
     */
    public static function getQuantity()
    {
        // Get the authenticated customer's cart.
        $cart = Auth::user()->cart;

        // Check if the cart exists, and if not, throw an exception.
        if (!$cart) throw new EmptyCartException();

        // Return the total quantity of items in the cart.
        return $cart->quantity;
    }

    /**
     * Get the address stored in the cart.
     *
     * @return string The address stored in the cart.
     *
     * @throws EmptyCartException If the cart is empty (not found).
     */
    public static function getAddress()
    {
        // Get the authenticated customer's cart.
        $cart = Auth::user()->cart;

        // Check if the cart exists, and if not, throw an exception.
        if (!$cart) throw new EmptyCartException();

        // Return the address stored in the cart.
        return $cart->address;
    }

    /**
     * Store prescriptions in the cart.
     *
     * @param Request $request The HTTP request containing the prescription files.
     *
     * @throws EmptyCartException If the cart is empty (not found).
     */
    public static function storePrescriptions(Request $request)
    {
        // Validate the request data.
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'max:4096', // Assuming maximum file size is 4096 KB (4 MB).
        ]);

        // Get the authenticated customer's cart.
        $cart = $request->user()->cart;

        // Check if the cart exists, and if not, throw an exception.
        if (!$cart) throw new EmptyCartException();

        // Store each prescription file in the cart.
        $files = $request->file('files');
        foreach ($files as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = "{$originalName}-{$cart->customer_id}.{$file->getClientOriginalExtension()}";
            Storage::disk('local')->put($filename, File::get($file));
            $prescription = ['prescription' => $filename];
            $cart->cartedPrescriptions()->create($prescription);
        }
    }


    /**
     * Check if prescriptions are uploaded in the cart.
     *
     * @return bool Returns true if prescriptions are uploaded, false otherwise.
     *
     * @throws EmptyCartException If the cart is empty (not found).
     */
    public static function checkPrescriptionsUpload()
    {
        // Get the authenticated customer's cart.
        $cart = Auth::user()->cart;

        // Check if the cart exists, and if not, throw an exception.
        if (!$cart) throw new EmptyCartException;

        // Return true if prescriptions are uploaded in the cart, otherwise false.
        return $cart->cartedPrescriptions->count() != 0 ? true : false;
    }

    /**
     * Validate the checkout request before processing the payment.
     *
     * @param Request $request The HTTP request containing the checkout data.
     *
     * @throws NullAddressException If the address in the checkout request is null.
     * @throws EmptyCartException If the cart is empty (not found).
     * @throws NotEnoughMoneyException If the customer doesn't have enough money to pay for the cart.
     */
    protected static function validateCheckout(Request $request)
    {
        // Get the authenticated customer.
        $customer = $request->user();

        // Get the customer's cart.
        $cart = $customer->cart;

        // Check if the address in the checkout request is null, and if so, throw an exception.
        if ($request->address == null) {
            throw new NullAddressException();
        }

        // Check if the cart exists, and if not, throw an exception.
        if (!$cart) throw new EmptyCartException();

        // Check if the customer has enough money to pay for the cart, and if not, throw an exception.
        if ($customer->money < $cart->total) throw new NotEnoughMoneyException();
    }

    /**
     * Check if the cart contains prescription-required products and prescriptions are uploaded.
     *
     * @param Cart $cart The cart to be checked.
     *
     * @throws PrescriptionRequiredException If prescription-required products are in the cart, but no prescriptions are uploaded.
     *
     * @return bool Returns true if the cart contains prescription-required products, false otherwise.
     */
    protected static function checkPrescriptionProducts(Cart $cart)
    {
        $containsPrescriptionProducts = false;

        foreach ($cart->cartedProducts as $product) {
            // Check if the cart contains prescription-required products.
            if ($product->purchasedProduct->product->otc == 0) {
                $containsPrescriptionProducts = true;
            }
        }

        // Check if the cart contains prescription-required products, but no prescriptions are uploaded.
        if ($cart->cartedPrescriptions->count() == 0 && $containsPrescriptionProducts) {
            throw new PrescriptionRequiredException();
        }

        return $containsPrescriptionProducts;
    }


    /**
     * Process the payment for the cart by deducting the total amount from the customer's balance and adding it to the pharmacy's balance.
     *
     * @param Cart $cart The cart to process the payment for.
     */
    protected static function processPayment(Cart $cart)
    {
        // Get the customer associated with the cart.
        $customer = $cart->customer;

        // Perform the payment transaction in a database transaction to ensure consistency.
        DB::transaction(function () use ($customer, $cart) {
            // Deduct the total amount from the customer's money.
            $customer->decrement('money', $cart->total);
            // Get the first pharmacy (assuming there's only one) and add the total amount to its balance.
            $pharmacy = Pharmacy::first();
            // add the total amount to the pharmacy's money.
            $pharmacy->increment('money', $cart->total);
        });
    }

    /**
     * Create an order based on the cart contents.
     *
     * @param Cart $cart The cart to create the order for.
     *
     * @return Order The newly created order.
     */
    protected static function createOrder(Cart $cart)
    {
        // Check if the cart contains prescription-required products.
        $containsPrescriptionProducts = self::checkPrescriptionProducts($cart);

        // Determine the initial status of the order based on the presence of prescription-required products.
        $initialStatusId = $containsPrescriptionProducts
            ? OrderStatus::where('name', 'Review')->value('id')
            : OrderStatus::where('name', 'Delivery')->value('id');

        // Create a new order based on the cart information and the initial status.
        $order = Order::create([
            'customer_id' => $cart->customer->id,
            'total' => $cart->total,
            'shipping_fees' => $cart->shipping_fees ?? 0,
            'shipping_address' => $cart->shipping_address ?? "Damascus",
            'quantity' => $cart->quantity,
            'status_id' => $initialStatusId,
        ]);

        // Create ordered products associated with the order.
        self::createOrderedProducts($cart, $order);

        // Create prescriptions associated with the order (if any).
        self::createPrescriptions($cart, $order);

        // Return the newly created order.
        return $order;
    }

    /**
     * Create ordered products associated with the given order based on the carted products in the cart.
     *
     * @param Cart $cart The cart containing the carted products.
     * @param Order $order The order to associate the ordered products with.
     */
    protected static function createOrderedProducts(Cart $cart, Order $order)
    {
        // Get all carted products associated with the cart.
        $cartedProducts = $cart->cartedProducts;

        // Create ordered products based on the carted products.
        foreach ($cartedProducts as $cartedProduct) {
            OrderedProduct::create([
                'order_id' => $order->id,
                'purchased_product_id' => $cartedProduct->purchased_product_id,
                'quantity' => $cartedProduct->quantity,
                'subtotal' => $cartedProduct->subtotal,
            ]);
        }
    }

    /**
     * Create prescriptions associated with the given order based on the carted prescriptions in the cart.
     *
     * @param Cart $cart The cart containing the carted prescriptions.
     * @param Order $order The order to associate the prescriptions with.
     */
    protected static function createPrescriptions(Cart $cart, Order $order)
    {
        // Get all carted prescriptions associated with the cart.
        $cartedPrescriptions = $cart->cartedPrescriptions;

        // Create prescriptions based on the carted prescriptions.
        foreach ($cartedPrescriptions as $cartedPrescription) {
            // Generate a new name for the prescription file by including the order ID to avoid conflicts.
            $newPrescriptionName = "{$order->id}-{$cartedPrescription->prescription}";

            // Move the prescription file to the new location (assuming the disk is set up correctly).
            Storage::disk('local')->move($cartedPrescription->prescription, $newPrescriptionName);

            // Create a new Prescription entry associated with the order.
            Prescription::create([
                'order_id' => $order->id,
                'prescription' => $newPrescriptionName,
            ]);
        }
    }


    /**
     * Relationships
     */

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function cartedProducts()
    {
        return $this->hasMany(CartedProduct::class, 'customer_id', 'customer_id');
    }
    public function cartedPrescriptions()
    {
        return $this->hasMany(CartedPrescription::class, 'customer_id', 'customer_id');
    }
}
