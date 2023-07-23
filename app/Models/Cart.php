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

    public static function addItem(Product $product, Request $request)
    {
        $product->isAvailble();

        $customer = $request->user();

        $item = $product->getEarliestExpiryDateProduct();

        $request->validate([
            'quantity' => 'required'
        ]);

        $quantity = $request->quantity;
        if ($quantity > $item->order_limit) {
            throw new QuantityExceededOrderLimitException();
        }

        if ($item->quantity - $quantity < $item->minimum_stock_level) {
            throw new OutOfStockException();
        }

        $subtotal = $item->price * $quantity;
        $cart = self::firstOrNew();
        $cart->customer_id = $customer->id;
        $cart->save();

        CartedProduct::create([
            'customer_id' => $cart->customer_id,
            'purchased_product_id' => $item->id,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ]);

        $item->decrement('quantity', $quantity);
        self::updateTotal($cart);
        self::updateCartQuantity($cart);
    }

    public static function removeItem(CartedProduct $item)
    {
        $cart = $item->cart;
        $cartedProductsCount = $cart->cartedProducts->count();
        $quantity = $item->quantity;
        $item->increment('quantity', $quantity);
        $item->delete();
        self::updateTotal($cart);
        self::updateCartQuantity($cart);
        if ($cartedProductsCount == 1) {
            $cart->cartedPrescriptions->each(function ($cartedPrescription) {
                Storage::disk('local')->delete($cartedPrescription->prescription);
                $cartedPrescription->delete();
            });
            $cart->delete();
        }
    }

    public static function updateQuantity(CartedProduct $item, Request $request)
    {
        $quantity = $request->quantity;

        $request->validate([
            'quantity' => 'required'
        ]);

        if ($quantity > $item->purchasedProduct->order_limit) {
            throw new QuantityExceededOrderLimitException();
        }

        if ($item->purchasedProduct->quantity - $quantity < $item->minimum_stock_level) {
            throw new OutOfStockException();
        }

        $item->quantity = $quantity;
        $item->subtotal = $item->quantity * $item->purchasedProduct->price;
        $item->save();
        $cart = $item->cart;
        self::updateTotal($cart);
        self::updateCartQuantity($cart);
    }

    private static function updateTotal(self $cart)
    {
        $cartedProducts = $cart->cartedProducts;
        $total = $cartedProducts->sum('subtotal');
        $cart->total = $total;
        $cart->save();
        return $total;
    }
    private static function updateCartQuantity(self $cart)
    {
        $cartedProducts = $cart->cartedProducts;
        $quantity = $cartedProducts->sum('quantity');
        $cart->quantity = $quantity;
        $cart->save();
        return $quantity;
    }

    public static function getTotal()
    {
        $customer = Auth::user();
        $cart = $customer->cart;
        if (!$cart) throw new EmptyCartException();
        return $customer->cart->total;
    }

    public static function storeAdress(Request $request)
    {
        $request->validate([
            'address' => 'required',
        ]);
        $cart = Auth::user()->cart;
        if (!$cart) throw new EmptyCartException();
        $cart->address = $request->address;
        $cart->save();
        return $request->address;
    }

    public static function checkout(Request $request)
    {
        self::validateCheckout($request);

        $cart = $request->user()->cart;

        $cart->address = $request->address;

        self::processPayment($cart);

        $order = self::createOrder($cart);

        self::clear($cart);

        Mail::to($request->user())->send(new OrderUnderReview($order));
    }

    public static function clear()
    {
        $cart = Auth::user()->cart;
        $cartedProducts = $cart->cartedProducts;
        if (count($cartedProducts) == 0) {
            throw new EmptyCartException();
        }
        $cartedProducts->each(function ($cartedProduct) {
            $cartedProduct->delete();
        });
        $cartedPrescriptions = $cart->cartedPrescriptions;
        $cartedPrescriptions->each(function ($cartedPrescription) {
            Storage::disk('local')->delete($cartedPrescription->prescription);
            $cartedPrescription->delete();
        });
        $cart->delete();
    }


    public static function getQuantity()
    {
        $cart = Auth::user()->cart;
        if (!$cart) {
            throw new EmptyCartException();
        }
        return $cart->quantity;
    }

    public static function getAddress()
    {
        $cart = Auth::user()->cart;
        if (!$cart) {
            throw new EmptyCartException();
        }
        return $cart->address;
    }

    public static function storePrescriptions(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'max:4096'
        ]);
        $cart = $request->user()->cart;

        if (!$cart) throw new EmptyCartException;

        $files = $request->file('files');
        foreach ($files as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = "{$originalName}-{$cart->customer_id}.{$file->getClientOriginalExtension()}";
            Storage::disk('local')->put($filename, File::get($file));
            $prescription = ['prescription' => $filename];
            $cart->cartedPrescriptions()->create($prescription);
        }
    }

    public static function checkPrescriptionsUpload()
    {
        $cart = Auth::user()->cart;
        if (!$cart) {
            throw new EmptyCartException;
        }
        return $cart->cartedPrescriptions->count() != 0
            ? true
            : false;
    }


    protected static function validateCheckout(Request $request)
    {
        $customer = $request->user();
        $cart = $customer->cart;
        if ($request->address == null) {
            throw new NullAddressException();
        }

        if (!$cart) {
            throw new EmptyCartException();
        }

        if ($customer->money < $cart->total) {
            throw new NotEnoughMoneyException();
        }
    }

    protected static function checkPrescriptionProducts(Cart $cart)
    {
        $containsPrescriptionProducts = 0;
        foreach ($cart->cartedProducts as $product) {
            if ($product->purchasedProduct->product->otc == 0) {
                $containsPrescriptionProducts = true;
            }
        }

        if ($cart->cartedPrescriptions->count() == 0 && $containsPrescriptionProducts == 1) {
            throw new PrescriptionRequiredException();
        }

        return $containsPrescriptionProducts;
    }

    protected static function processPayment(Cart $cart)
    {
        $customer = $cart->customer;
        DB::transaction(function () use ($customer, $cart) {
            $customer->money -= $cart->total;
            $pharmacy = Pharmacy::first();
            $pharmacy->money += $cart->total;
            $customer->save();
            $pharmacy->save();
        });
    }


    protected static function createOrder(Cart $cart)
    {
        $containsPrescriptionProducts = self::checkPrescriptionProducts($cart);

        $order = Order::create([
            'customer_id' => $cart->customer->id,
            'total' => $cart->total,
            'shipping_fees' => $cart->shipping_fees ?? 0,
            'shipping_address' => $cart->shipping_address ?? "Damascus",
            'quantity' => $cart->quantity,
            'status_id' => $containsPrescriptionProducts ? OrderStatus::where('name', 'Review')->value('id') : OrderStatus::where('name', 'Delivery')->value('id'),
        ]);

        self::createOrderedProducts($cart, $order);
        self::createPrescriptions($cart, $order);

        return $order;
    }

    protected static function createOrderedProducts(Cart $cart, Order $order)
    {
        $cartedProducts = $cart->cartedProducts;
        foreach ($cartedProducts as $cartedProduct) {
            OrderedProduct::create([
                'order_id' => $order->id,
                'purchased_product_id' => $cartedProduct->purchased_product_id,
                'quantity' => $cartedProduct->quantity,
                'subtotal' => $cartedProduct->subtotal,
            ]);
        }
    }

    protected static function createPrescriptions(Cart $cart, Order $order)
    {
        $cartedPrescriptions = $cart->cartedPrescriptions;
        foreach ($cartedPrescriptions as $cartedPrescription) {
            $newPrescriptionName = "{$order->id}-{$cartedPrescription->prescription}";
            Storage::disk('local')->move($cartedPrescription->prescription, $newPrescriptionName);
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
