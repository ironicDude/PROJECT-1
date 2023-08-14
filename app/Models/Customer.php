<?php

namespace App\Models;

use App\Http\Resources\Order\OrderOverviewCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;

class Customer extends User
{
    use HasFactory;
    use SingleTableInheritanceTrait;
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

    public static function chartNewbiesAndBastards(string $date, string $period)
    {
        $points = collect();
        $counter = 0;
        switch ($period) {
            case 'day':
                for ($i = 23; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subHours($i);
                    $end = Carbon::parse($date)->subHours($i - 1);
                    $newbies = Customer::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $bastards = Customer::withTrashed()
                        ->where('deleted_at', '>=', $start)
                        ->where('deleted_at', '<', $end)
                        ->count();
                    $points->push([
                        'hour' => $counter++,
                        'newbies' => $newbies,
                        'bastards' => $bastards
                    ]);
                }
                break;
            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $newbies = Customer::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $bastards = Customer::withTrashed()
                        ->where('deleted_at', '>=', $start)
                        ->where('deleted_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $counter++,
                        'newbies' => $newbies,
                        'bastards' => $bastards
                    ]);
                }
                break;
            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $newbies = Customer::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $bastards = Customer::withTrashed()
                        ->where('deleted_at', '>=', $start)
                        ->where('deleted_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $counter++,
                        'newbies' => $newbies,
                        'bastards' => $bastards
                    ]);
                }
                break;
            case 'year':
                for ($i = 364; $i >= 0; $i--) {
                    $start = Carbon::parse($date)->subDays($i);
                    $end = Carbon::parse($date)->subDays($i - 1);
                    $newbies = Customer::where('created_at', '>=', $start)
                        ->where('created_at', '<', $end)
                        ->count();
                    $bastards = Customer::withTrashed()
                        ->where('deleted_at', '>=', $start)
                        ->where('deleted_at', '<', $end)
                        ->count();
                    $points->push([
                        'day' => $counter++,
                        'newbies' => $newbies,
                        'bastards' => $bastards
                    ]);
                }
                break;
        }

        return $points;
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
