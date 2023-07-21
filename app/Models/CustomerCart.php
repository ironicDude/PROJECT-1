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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\ItemNotFoundException;

class CustomerCart extends Model
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

        $customer = Auth::user();
        $item = $product->getEarliestExpiryDateProduct();
        $quantity = $request->quantity;
        $request->validate([
            'quantity' => 'required'
        ]);
        if ($quantity > $item->order_limit) {
            throw new QuantityExceededOrderLimitException();
        }

        if ($item->quantity - $quantity < $item->minimum_stock_level) {
            throw new OutOfStockException();
        }

        $subtotal = $item->price * $quantity;
        $cart = CustomerCart::firstOrNew(['customer_id' => $customer->id]);
        $cart->customer_id = $customer->id;
        $cart->save();
        $cartItem = CartedProduct::create([
            'customer_id' => $cart->customer_id,
            'purchased_product_id' => $item->id,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ]);
        $cartItem->save();
        $item->decrement('quantity', $quantity);
        $item->save();
        self::updateTotal($cart);
        self::updateCartQuantity($cart);
    }

    public static function removeItem(CartedProduct $item)
    {
        $cart = $item->cart;
        $quantity = $item->quantity;
        $item->increment('quantity', $quantity);
        $item->save();
        $item->delete();
        self::updateTotal($cart);
        self::updateCartQuantity($cart);
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

    private static function updateTotal(CustomerCart $cart)
    {
        $cartedProducts = $cart->cartedProducts;
        $total = $cartedProducts->sum('subtotal');
        $cart->total = $total;
        $cart->save();
    }
    private static function updateCartQuantity(CustomerCart $cart)
    {
        $cartedProducts = $cart->cartedProducts;
        $quantity = $cartedProducts->sum('quantity');
        $cart->quantity = $quantity;
        $cart->save();
    }

    public static function getTotal()
    {
        $customer = Auth::user();
        $cart = $customer->cart;
        if (!$cart) {
            return new EmptyCartException();
        }
        return $customer->cart->total;
    }

    public static function storeAdress(Request $request)
    {
        $request->validate([
            'address' => 'required',
        ]);
        $cart = Auth::user()->cart;
        if (!$cart) {
            return new EmptyCartException();
        }
        $cart->address = $request->address;
        $cart->save();
    }

    public static function checkout()
    {
        $customer = Auth::user();
        $cart = $customer->cart;
        if ($cart->address == null) {
            throw new NullAddressException();
        }
        if (!$cart) {
            throw new EmptyCartException();
        }
        if ($customer->money < $cart->total) {
            throw new NotEnoughMoneyException();
        }
        $cartedProduct = $cart->cartedProducts();
        $cartedProduct->each(function ($cartedProduct) {
            if ($cartedProduct->purchasedProduct->product->otc == 0) {
                throw new PrescriptionRequiredException();
            }
        });

        $customer->money -= $cart->total;
        $pharmacy = Pharmacy::first();
        $pharmacy->money += $cart->total;

        $customer->save();
        $pharmacy->save();
        self::clear();
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
        $cart->delete();
    }

    public static function getQuantity()
    {
        $cart = Auth::user()->cart;
        if (!$cart) {
            return new EmptyCartException();
        }
        return $cart->quantity;
    }

    public static function getAddress()
    {
        $cart = Auth::user()->cart;
        if (!$cart) {
            return new EmptyCartException();
        }
        return $cart->address;
    }


    public static function storePrescriptions(Request $request)
    {
        $cart = Auth::user()->cart;
        if (!$cart) {
            return new EmptyCartException();
        }
        $counter = 1;
        $files = $request->file('files');
            foreach ($files as $file) {
            $fileName = "prescription-{$cart->customer_id}-{$counter}";
            $file->storeAs('public/prescriptions', $fileName);
            $prescription = ['prescription' => 'public/prescriptions/' . $fileName];
            $cart->cartedPrescriptions()->create($prescription);
            $counter++;
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
