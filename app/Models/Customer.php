<?php

namespace App\Models;

use App\Http\Resources\Order\OrderOverviewCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Customer extends User
{
    use HasFactory;
    protected static $singleTableType = 'customer';
    protected static $persisted = ['money'];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'date_of_birth',
        'gender',
        'image',
        'money',
    ];



    /**
     * Get a paginated list of orders associated with this customer.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator The paginated collection of orders.
     */
    public function viewOrders()
    {
        // Retrieve the orders associated with this customer using the 'orders' relationship and paginate the results with 10 orders per page.
        $orders = $this->orders()->paginate(10);

        // Wrap the paginated orders collection in an 'OrderOverviewCollection' resource to customize the response format.
        return $orders;
    }

    public function createCart()
    {
        $cart = Cart::firstOrNew(['id' => $this->id]);
        $cart->save();
        return $cart;
    }

    public function getMoney()
    {
        return $this->momey;
    }

    public static function countNewbies(int $days)
    {
        $date = Carbon::now()->subDays($days);
        $count = Customer::all()->where('created_at', '>=', $date)->count();
        return $count;
    }

    /**
     * Relationships
     */

    public function cart()
    {
        return $this->hasOne(Cart::class, 'id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
}
