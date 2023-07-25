<?php

namespace App\Models;

use App\Http\Resources\OrderOverviewCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'gender_id',
        'image',
        'money',
    ];



    public function viewOrders()
    {
        $orders = $this->orders()->paginate(10);
        return new OrderOverviewCollection($orders);
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
