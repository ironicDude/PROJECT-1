<?php

namespace App\Models;

use App\Events\MinimumStockLevelExceeded;
use App\Exceptions\InShortageException;
use App\Exceptions\ItemAlreadyInCartException;
use App\Exceptions\ItemNotInCartException;
use App\Exceptions\NoPrescriptionsException;
use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\NullAddressException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\ProductAlreadyAddedException;
use App\Exceptions\ProductAlreadyInCartException;
use App\Exceptions\ProductNotAddedException;
use App\Exceptions\QuantityExceededOrderLimitException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Mail\OrderUnderReview;
use App\Notifications\MinimumStockLevelExceededNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class Cart extends Model
{
    use HasFactory;
    use CustomResponse;

    protected $fillable = [
        'id',
        'quantity',
        'subtotal'
    ];


    public function addProduct(PurchasedProduct $product, int $quantity)
    {
        DB::transaction(function () use ($product, $quantity) {

            if ($this->getPurchasedProductItems($product)->count() > 0) {
                throw new ProductAlreadyAddedException();
            }

            $this->validateStock($product, $quantity);

            $itemsData = $this->chooseItems($product, $quantity);

            $this->createItems($itemsData);
        });
    }

    protected function validateStock(PurchasedProduct $product, int $quantity)
    {
        if(!$product->isAvailable()){
            throw new OutOfStockException();
        }

        if($quantity > $product->getOrderLimit()){
            throw new QuantityExceededOrderLimitException();
        }

        DB::commit();
        if (!$product->isMinimumStockLevelSafe() && $quantity > 1) {
            $allowedQuantity = 1;
            $this->updateQuantityAndThrowShortageException($product, $allowedQuantity);
        }

        if($product->isMinimumStockLevelSafe() && $quantity > $product->getSafeDistance()){
            $allowedQuantity = $product->getSafeDistance();
            $this->updatequantityAndThrowShortageException($product, $allowedQuantity);
        }
    }

    protected function updateQuantityAndThrowShortageException(PurchasedProduct $product, int $quantity)
    {
        $productName = $product->getName();
        $this->updateQuantity($product, $quantity);
        throw new InShortageException("Unfortunately, {$productName} is in shortage. We modified its quantity in your cart to {$quantity}, which is as high as we can offer at the moment. If you don't prefer a partial fulfillment, you can press delete to remove the product from the cart");
    }

    protected function chooseItems(PurchasedProduct $product, int $quantity)
    {
        $items = [];
        $quantities = [];
        $itemIds = [];
        $tempQuantity = $quantity;
        while ($tempQuantity > 0) {
            $item = $product->datedProducts()->where('quantity', '>', 0)->whereNotIn('id', $itemIds)->whereNotNull('expiry_date')->orderBy('expiry_date')->first();
            $itemIds[] = $item->id;
            if (!$item) {
                throw new OutOfStockException();
            }
            if ($item->quantity >= $tempQuantity) {
                $quantities[] = $tempQuantity;
                $tempQuantity = 0;
            } else {
                $quantities[] = $item->quantity;
                $tempQuantity -= $item->quantity;
            }
            $items[] = $item;
        }
        return [
            'items' => $items,
            'quantities' => $quantities
        ];
    }

    protected function createItems(array $itemsData)
    {
        $items = $itemsData['items'];
        $quantities = $itemsData['quantities'];
        $counter = 0;
        foreach ($items as $item) {
            $subtotal = $item->purchasedProduct->price * $quantities[$counter];
            CartedProduct::create([
                'cart_id' => Auth::user()->id,
                'dated_product_id' => $item->id,
                'quantity' => $quantities[$counter],
                'subtotal' => $subtotal,
            ]);
            $counter++;
        }
    }
    public function removeProduct(PurchasedProduct $product)
    {
        if($this->getPurchasedProductItems($product)->count() == 0) throw new ProductNotAddedException();
        DB::transaction(function () use ($product) {
            $this->deletePurchasedProductItems($product);
            $this->load('cartedProducts');
            if ($this->cartedProducts->count() == 0) {
                $this->deletePrescriptions();
                $this->delete();
            }
        });
        return $product;
    }

    public function updateQuantity(PurchasedProduct $product, int $newQuantity)
    {
        $this->deletePurchasedProductItems($product);
        $this->load('cartedProducts');
        // dd($this->cartedProducts);
        $this->addProduct($product, $newQuantity);
        return $newQuantity;
    }

    protected function getPurchasedProductItems(PurchasedProduct $product)
    {
        $datedProductIds = $product->datedProducts->pluck('id');
        return $this->cartedProducts->whereIn('dated_product_id', $datedProductIds);
    }

    protected function deletePurchasedProductItems(PurchasedProduct $product)
    {
        $datedProductIds = $product->datedProducts->pluck('id');
        $this->cartedProducts->whereIn('dated_product_id', $datedProductIds)->each->delete();
    }

    /**
     * Get the total quantity of carted products in the cart.
     *
     * @return int The total quantity of carted products.
     */
    public function getQuantity()
    {
        return $this->cartedProducts->sum('quantity');
    }

    /**
     * Get the total value of the cart.
     *
     * @return float The total value of the cart.
     */
    public function getTotal()
    {
        return $this->cartedProducts->sum('subtotal');
    }

    /**
     * Store the user's address for the cart.
     *
     * @param Request $request The HTTP request containing the user's address.
     * @return string The stored address.
     */
    public function storeAdress(string $address)
    {

        // Store the user's address in the cart.
        $this->address = $address;
        $this->save();

        // Return the stored address.
        return $this->address;
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
     * Process the checkout for the cart.
     *
     * @param Request $request The HTTP request containing the necessary checkout information.
     */
    public function checkout(string $address)
    {
        // Perform the payment transaction within a database transaction to ensure data consistency.
        DB::transaction(function () use ($address) {
            // Validate the checkout request.
            $this->validateCheckout($address);

            // Get the customer associated with the cart.
            $customer = Auth::user();

            // Update the cart's address with the one provided in the checkout request.
            $this->address = $address;

            //process the stock for this order
            $this->processStock();

            // Process the payment for the cart.
            $this->processPayment();

            // Create an order based on the current cart.
            $order = $this->createOrder();

            // Clear the cart (remove all carted products and prescriptions).
            $this->clear();

            // Send an email to the customer with the order details for review.
            Mail::to($customer)->send(new OrderUnderReview($order));
        });
    }

    /**
     * Validate the checkout request before processing the payment and creating the order.
     *
     * @param Request $request The HTTP request containing the checkout information.
     * @throws NullAddressException If the address is not provided in the request.
     * @throws NotEnoughMoneyException If the customer does not have enough money to complete the purchase.
     */
    protected function validateCheckout(string $address)
    {
        // Get the customer associated with the cart.
        $customer = Auth::user();

        // Check if the address is provided in the request.
        if ($address == null) throw new NullAddressException();

        // Check if the customer has enough money to complete the purchase.
        if ($customer->money < $this->total) throw new NotEnoughMoneyException();

        $this->checkPrescriptionProducts();
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
            if ($product->datedProduct->purchasedProduct->product->otc == 0) {
                $containsPrescriptionProducts = true;
            }
        }

        // If there are prescription products in the cart but no prescriptions uploaded, throw an exception.
        if ($this->checkForPrescriptions() == false && $containsPrescriptionProducts) {
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
            $total = $this->getTotal();
            $customer->decrement('money', $total);
            $pharmacy = Pharmacy::first();
            $pharmacy->increment('money', $total);
        });
    }

    protected function processStock()
    {
        // Get the customer associated with the cart.
        $cartedProducts = $this->cartedProducts;

        // Perform the payment transaction within a database transaction to ensure data consistency.
        DB::transaction(function () use ($cartedProducts) {
            $purchasedProducts = [];
            foreach ($cartedProducts as $cartedProduct) {
                // Check if there is enough stock before updating quantities
                $purchasedProduct = $cartedProduct->datedProduct->purchasedProduct;
                if (!in_array($purchasedProduct, $purchasedProducts)) {
                    $quantityInCart = $this->getPurchasedProductItems($purchasedProduct)->sum('quantity');
                    $this->validateStock($purchasedProduct, $quantityInCart);
                }
                $cartedProduct->datedProduct()->decrement('quantity', $cartedProduct->quantity);
                $purchasedProducts[] = $purchasedProduct;
                if($purchasedProduct->getQuantity() < $purchasedProduct->getMinimumStockLevel()){
                    $inventoryManager = Employee::whereRelation('roles','role', 'inventory manager')->first();
                    $admin = Employee::whereRelation('roles','role', 'administrator')->first();
                    event(new MinimumStockLevelExceeded($purchasedProduct, $inventoryManager, $admin));
                    $admin->notify(new MinimumStockLevelExceededNotification($purchasedProduct));
                }
            }
        });
    }

    /**
     * Create an order based on the cart contents.
     *
     * @return Order The newly created order instance.
     */
    protected function createOrder()
    {

        // Create a new order entry in the database.
        $order = OnlineOrder::create([
            'customer_id' => $this->customer->id,
            'shipping_fees' => !$this->shipping_fees ? 0 : $this->shipping_fees,
            'shipping_address' => !$this->address ? 'Damascus' : $this->address,
            'status_id' => 1,
            'method_id' => 1,
        ]);

        // Create ordered product entries for each carted product in the order.
        $this->createOrderedProducts($order);

        // Create prescription entries for each carted prescription in the order.
        $this->createPrescriptions($order);

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
                'dated_product_id' => $cartedProduct->dated_product_id,
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
     * Store prescriptions uploaded by the user in the cart.
     *
     * @param Request $request The HTTP request containing the uploaded prescription files.
     * @return array The names of the stored prescription files.
     */
    public function storePrescriptions($files)
    {

        $fileNames = [];
        foreach ($files as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $noSpaceOriginalName = str_replace(' ', '', $originalName);
            $filename = "{$noSpaceOriginalName}-{$this->id}.{$file->getClientOriginalExtension()}";
            $fileNames[] = $filename;
            Storage::disk('local')->put($filename, File::get($file));
            $prescription = ['prescription' => $filename];
            $this->cartedPrescriptions()->create($prescription);
        }

        // Return the names of the stored prescription files.
        return $fileNames;
    }

    public function deletePrescriptions()
    {
        $prescriptions = $this->cartedPrescriptions;
        foreach($prescriptions as $prescription)
        {
            Storage::disk('local')->delete($prescription->prescription);
            $prescription->delete();
        }
        return $prescriptions->pluck('prescription');
    }

    /**
     * Check if there are any prescriptions uploaded in the cart.
     *
     * @return bool True if there are prescriptions uploaded, false otherwise.
     */
    public function checkForPrescriptions()
    {
        // Check if there are any carted prescriptions in the cart.
        return $this->cartedPrescriptions->count() > 0 ? true : false;
    }
    /**
     * Clear the cart by removing all carted products and prescriptions.
     */
    public function clear()
    {
        DB::transaction(function () {
            $this->deleteProducts();

            $this->deletePrescriptions();

            $this->delete();
        });
    }

    protected function deleteProducts()
    {
        $this->cartedProducts->each->delete();
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


    public function viewPrescriptions()
    {
        $filePaths = $this->cartedPrescriptions->pluck('prescription')->toArray();

        $data = [];
        foreach ($filePaths as $path) {
            $fileContents = file_get_contents("C:\Users\Elyas\Desktop\PROJECT-1\PROJECT-1\storage\app\\{$path}");
            $encodedContents = base64_encode($fileContents);
            if (pathinfo($path, PATHINFO_EXTENSION) === 'pdf') {
                $data[] = mb_convert_encoding("data:application/pdf;base64,{$encodedContents}", 'UTF-8');
            } else {
                $imgExtension = pathinfo($path, PATHINFO_EXTENSION);
                $data[] = mb_convert_encoding("data:image/{$imgExtension};base64,{$encodedContents}", 'UTF-8');
            }
        }
        return $data;
    }

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
