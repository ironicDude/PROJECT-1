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
use App\Http\Resources\CartedProductResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\PurchasedProductResource;
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
        'id',
        'quantity',
        'subtotal'
    ];
    /**
     * Add a product to the user's cart.
     *
     * @param Product $product The product to be added to the cart.
     * @param Request $request The HTTP request containing the product quantity.
     * @return CartedProductResource The newly added carted product resource.
     * @throws QuantityExceededOrderLimitException If the requested quantity exceeds the product's order limit.
     * @throws OutOfStockException If the product is out of stock.
     */
    public static function addItem(Product $product, Request $request)
    {
        // Validate the request to ensure the quantity is provided and valid.
        $request->validate([
            'quantity' => 'required|numeric|min:1',
        ]);

        // Check if the product is available for purchase.
        $product->isAvailble();

        // Get the product with the earliest expiry date for inventory management.
        $item = $product->getEarliestExpiryDateProduct();

        // Get the requested quantity from the request.
        $quantity = $request->quantity;

        // Check if the requested quantity exceeds the product's order limit.
        if ($quantity > $item->order_limit) {
            throw new QuantityExceededOrderLimitException();
        }

        // Check if there is enough stock for the requested quantity.
        if ($item->quantity - $quantity < $item->minimum_stock_level) {
            throw new OutOfStockException();
        }

        // Calculate the subtotal for the carted product.
        $subtotal = $item->price * $quantity;

        // Create or retrieve the user's cart.
        $cart = self::firstOrNew([
            'id' => Auth::user()->id
        ]);
        $cart->save();

        // Create a new carted product entry for the user's cart.
        $cartedProduct = CartedProduct::create([
            'cart_id' => Auth::user()->id,
            'purchased_product_id' => $item->id,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ]);

        // Decrement the product quantity in the inventory.
        $item->decrement('quantity', $quantity);

        // Update the cart's total and quantity.
        self::updateTotal($cart);
        self::updateCartQuantity($cart);

        // Return the carted product resource for the newly added item.
        return new CartedProductResource($cartedProduct);
    }

    /**
     * Remove a carted product item from the user's cart.
     *
     * @param CartedProduct $item The carted product to be removed.
     * @return CartedProductResource The removed carted product resource.
     */
    public function removeItem(CartedProduct $item)
    {
        // Get the count of carted products in the user's cart.
        $cartedProductsCount = $this->cartedProducts->count();

        // Increment the product quantity in the inventory as the item is removed from the cart.
        $quantity = $item->quantity;
        $item->purchasedProduct()->increment('quantity', $quantity);

        // Delete the carted product entry.
        $item->delete();

        // Update the cart's total and quantity.
        self::updateTotal($this);
        self::updateCartQuantity($this);

        // If the last carted product is removed, delete the cart and any associated prescriptions.
        if ($cartedProductsCount == 1) {
            $this->cartedPrescriptions->each(function ($cartedPrescription) {
                // Delete the associated prescription file from the storage.
                Storage::disk('local')->delete($cartedPrescription->prescription);
                $cartedPrescription->delete();
            });
            $this->delete();
        }

        // Return the carted product resource for the removed item.
        return new CartedProductResource($item);
    }

    /**
     * Update the quantity of a carted product in the user's cart.
     *
     * @param CartedProduct $item The carted product to be updated.
     * @param Request $request The HTTP request containing the updated quantity.
     * @return int The updated quantity of the carted product.
     * @throws QuantityExceededOrderLimitException If the updated quantity exceeds the product's order limit.
     * @throws OutOfStockException If the product is out of stock after the update.
     */
    public function updateQuantity(CartedProduct $item, Request $request)
    {
        // Get the requested quantity from the request.
        $quantity = $request->quantity;

        // Validate the request to ensure the updated quantity is provided and valid.
        $request->validate([
            'quantity' => 'required|numeric|min:1',
        ]);

        // Check if the updated quantity exceeds the product's order limit.
        if ($quantity > $item->purchasedProduct->order_limit) {
            throw new QuantityExceededOrderLimitException();
        }

        // Check if there is enough stock for the updated quantity.
        if ($item->purchasedProduct->quantity - $quantity < $item->minimum_stock_level) {
            throw new OutOfStockException();
        }

        // Update the carted product's quantity and subtotal.
        $item->quantity = $quantity;
        $item->subtotal = $item->quantity * $item->purchasedProduct->price;
        $item->save();

        // Update the cart's total and quantity.
        self::updateTotal($this);
        self::updateCartQuantity($this);

        // Return the updated quantity of the carted product.
        return $item->quantity;
    }

    /**
     * Update the total value of the cart.
     *
     * @param Cart $cart The user's cart.
     */
    protected static function updateTotal()
    {
        // Get the user's cart and its carted products.
        $cart = Auth::user()->cart;
        $cartedProducts = $cart->cartedProducts;

        // Calculate the total value of all carted products.
        $total = $cartedProducts->sum('subtotal');

        // Update the cart's total value.
        $cart->total = $total;
        $cart->save();
    }

    /**
     * Update the total quantity of carted products in the cart.
     *
     * @param Cart $cart The user's cart.
     */
    protected static function updateCartQuantity(Cart $cart)
    {
        // Get the user's cart and its carted products.
        $cart = Auth::user()->cart;
        $cartedProducts = $cart->cartedProducts;

        // Calculate the total quantity of all carted products.
        $quantity = $cartedProducts->sum('quantity');

        // Update the cart's total quantity.
        $cart->quantity = $quantity;
        $cart->save();
    }

    /**
     * Get the total value of the cart.
     *
     * @return float The total value of the cart.
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Store the user's address for the cart.
     *
     * @param Request $request The HTTP request containing the user's address.
     * @return string The stored address.
     */
    public function storeAdress(Request $request)
    {
        // Validate the request to ensure the address is provided.
        $request->validate([
            'address' => 'required',
        ]);

        // Store the user's address in the cart.
        $this->address = $request->address;
        $this->save();

        // Return the stored address.
        return $this->address;
    }

    /**
     * Process the checkout for the cart.
     *
     * @param Request $request The HTTP request containing the necessary checkout information.
     */
    public function checkout(Request $request)
    {
        // Validate the checkout request.
        self::validateCheckout($request);

        // Get the customer associated with the cart.
        $customer = $request->user();

        // Update the cart's address with the one provided in the checkout request.
        $this->address = $request->address;

        // Process the payment for the cart.
        self::processPayment($this);

        // Create an order based on the current cart.
        $order = self::createOrder($this);

        // Clear the cart (remove all carted products and prescriptions).
        self::clear($this);

        // Send an email to the customer with the order details for review.
        Mail::to($customer)->send(new OrderUnderReview($order));
    }

    /**
     * Clear the cart by removing all carted products and prescriptions.
     */
    public function clear()
    {
        // Delete all carted products associated with the cart.
        $this->cartedProducts->each->delete();

        // Delete all carted prescriptions and their associated files from storage.
        $cartedPrescriptions = $this->cartedPrescriptions;

        $cartedPrescriptions->each(function ($cartedPrescription) {
            Storage::disk('local')->delete($cartedPrescription->prescription);
            $cartedPrescription->delete();
        });

        // Delete the cart itself.
        $this->delete();
    }

    /**
     * Get the total quantity of carted products in the cart.
     *
     * @return int The total quantity of carted products.
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Get the address stored in the cart.
     *
     * @return string|null The address stored in the cart.
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Store prescriptions uploaded by the user in the cart.
     *
     * @param Request $request The HTTP request containing the uploaded prescription files.
     * @return array The names of the stored prescription files.
     */
    public function storePrescriptions(Request $request)
    {
        // Validate the request to ensure the prescription files are provided and within size limits.
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'max:4096',
        ]);

        // Store the uploaded prescription files and associate them with the cart.
        $files = $request->file('files');
        $fileNames = [];
        foreach ($files as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = "{$originalName}-{$this->customer_id}.{$file->getClientOriginalExtension()}";
            $fileNames[] = $filename;
            Storage::disk('local')->put($filename, File::get($file));
            $prescription = ['prescription' => $filename];
            $this->cartedPrescriptions()->create($prescription);
        }

        // Return the names of the stored prescription files.
        return $fileNames;
    }

    /**
     * Check if there are any prescriptions uploaded in the cart.
     *
     * @return bool True if there are prescriptions uploaded, false otherwise.
     */
    public function checkPrescriptionsUpload()
    {
        // Check if there are any carted prescriptions in the cart.
        return $this->cartedPrescriptions->count() != 0 ? true : false;
    }

    /**
     * Validate the checkout request before processing the payment and creating the order.
     *
     * @param Request $request The HTTP request containing the checkout information.
     * @throws NullAddressException If the address is not provided in the request.
     * @throws NotEnoughMoneyException If the customer does not have enough money to complete the purchase.
     */
    protected function validateCheckout(Request $request)
    {
        // Get the customer associated with the cart.
        $customer = $request->user();

        // Check if the address is provided in the request.
        if ($request->address == null) {
            throw new NullAddressException();
        }

        // Check if the customer has enough money to complete the purchase.
        if ($customer->money < $this->total) {
            throw new NotEnoughMoneyException();
        }
    }

    /**
     * Check if there are any prescription products in the cart.
     *
     * @return bool True if there are prescription products in the cart, false otherwise.
     * @throws PrescriptionRequiredException If there are prescription products in the cart but no prescriptions uploaded.
     */
    protected function checkPrescriptionProducts()
    {
        // Initialize a flag to check if the cart contains prescription products.
        $containsPrescriptionProducts = false;

        // Iterate through the carted products to find prescription products.
        foreach ($this->cartedProducts as $product) {
            if ($product->purchasedProduct->product->otc == 0) {
                $containsPrescriptionProducts = true;
            }
        }

        // If there are prescription products in the cart but no prescriptions uploaded, throw an exception.
        if ($this->cartedPrescriptions->count() == 0 && $containsPrescriptionProducts) {
            throw new PrescriptionRequiredException();
        }

        // Return whether prescription products are present in the cart.
        return $containsPrescriptionProducts;
    }

    /**
     * Process the payment for the cart by decrementing customer's money and incrementing pharmacy's money.
     */
    protected function processPayment()
    {
        // Get the customer associated with the cart.
        $customer = $this->customer;

        // Perform the payment transaction within a database transaction to ensure data consistency.
        DB::transaction(function () use ($customer) {
            $customer->decrement('money', $this->total);
            $pharmacy = Pharmacy::first();
            $pharmacy->increment('money', $this->total);
        });
    }

    /**
     * Create an order based on the cart contents.
     *
     * @return Order The newly created order instance.
     */
    protected function createOrder()
    {
        // Check if the cart contains prescription products.
        $containsPrescriptionProducts = $this->checkPrescriptionProducts();

        // Determine the initial status of the order based on whether prescription products are present.
        $initialStatusId = $containsPrescriptionProducts
            ? OrderStatus::where('name', 'Review')->value('id')
            : OrderStatus::where('name', 'Delivery')->value('id');

        // Create a new order entry in the database.
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'total' => $this->total,
            'shipping_fees' => $this->shipping_fees ?? 0,
            'shipping_address' => $this->shipping_address ?? "Damascus",
            'quantity' => $this->quantity,
            'status_id' => $initialStatusId,
        ]);

        // Create ordered product entries for each carted product in the order.
        self::createOrderedProducts($order);

        // Create prescription entries for each carted prescription in the order.
        self::createPrescriptions($order);

        // Return the newly created order instance.
        return $order;
    }

    /**
     * Create ordered product entries for the given order based on the carted products in the cart.
     *
     * @param Order $order The order for which ordered products are to be created.
     */
    protected function createOrderedProducts(Order $order)
    {
        // Get the carted products associated with the cart.
        $cartedProducts = $this->cartedProducts;

        // Create ordered product entries for each carted product in the order.
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
     * Create prescription entries for the given order based on the carted prescriptions in the cart.
     *
     * @param Order $order The order for which prescription entries are to be created.
     */
    protected function createPrescriptions(Order $order)
    {
        // Get the carted prescriptions associated with the cart.
        $cartedPrescriptions = $this->cartedPrescriptions;

        // Create prescription entries for each carted prescription in the order.
        foreach ($cartedPrescriptions as $cartedPrescription) {
            // Generate a new name for the prescription file based on the order ID and the original prescription name.
            $newPrescriptionName = "{$order->id}-{$cartedPrescription->prescription}";

            // Move the prescription file to a new location with the updated name.
            Storage::disk('local')->move($cartedPrescription->prescription, $newPrescriptionName);

            // Create a new prescription entry in the database.
            Prescription::create([
                'order_id' => $order->id,
                'prescription' => $newPrescriptionName,
            ]);
        }
    }

    /**
     * Get the cart instance for displaying or further processing.
     *
     * @return Cart The cart instance.
     */
    public function show()
    {
        return $this;
    }


    /**
     * Relationships
     */

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id', 'id');
    }
    public function cartedProducts()
    {
        return $this->hasMany(CartedProduct::class, 'cart_id', 'id');
    }
    public function cartedPrescriptions()
    {
        return $this->hasMany(CartedPrescription::class, 'cart_id', 'id');
    }
}
