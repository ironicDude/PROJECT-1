<?php

namespace App\Models;

use App\Events\BrokePharmacy;
use App\Exceptions\EmployeeAlreadyPaidException;
use App\Exceptions\PharmacyMoneyDrainingAmountException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payer_id',
        'employee_id',
        'amount',
    ];
    public static function make($amount = null, Employee $employee)
    {
        if(!$amount) {
            $amount = $employee->getSalary();
        }

        self::processPharmacyMoney($amount);

        $payment = Payment::create([
            'payer_id' => Auth::user()->id,
            'employee_id' => $employee->id,
            'amount' => $amount
        ]);
        return $payment;
    }

    public function edit(float $amount)
    {
        $this->undoPayment();
        Pharmacy::first()->decrement('money', $amount);
        if(Pharmacy::getMoney() < 0){
            $admin = Employee::getAdmin();
            event(new BrokePharmacy($admin));
        }
        $this->update(['amount' => $amount]);
        return $this;
    }

    protected static function processPharmacyMoney($amount)
    {
        Pharmacy::first()->decrement('money', $amount);
        if(Pharmacy::getMoney() < 0){
            $admin = Employee::getAdmin();
            event(new BrokePharmacy($admin));
        }
    }

    protected function undoPayment()
    {
        Pharmacy::first()->increment('money', $this->amount);
    }


    /**
     * Relationship
     */
     public function payer()
     {
        return $this->hasOne(Employee::class, 'payer_id', 'id');
     }

     public function receiver()
     {
        return $this->hasOne(Employee::class, 'employee_id', 'id');
     }
}
