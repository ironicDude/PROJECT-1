<?php

namespace App\Models;

use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\NullAddressException;
use App\Exceptions\ProductAlreadyAddedException;
use App\Exceptions\ProductNotAddedException;
use App\Mail\OrderRejected;
use App\Mail\OrderShipped;
use App\Mail\OrderUnderReview;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;

class OnlineOrder extends Order
{
    use HasFactory;
    use SingleTableInheritanceTrait;
    protected static $singleTableType = 'Online';
    protected static $persisted = [];

    protected function undo()
    {
        $this->undoPayment();
        $this->undoStock();
        $this->deletePrescriptions();
    }

    protected function undoPayment()
    {
        $customer = $this->customer;
        DB::transaction(function () use ($customer) {
            $total = $this->getTotal();
            $customer->increment('money', $total);
            $pharmacy = Pharmacy::first();
            $pharmacy->decrement('money', $total);
        });
    }

    protected function undoStock()
    {
        $products = $this->orderedProducts;
        foreach($products as $product){
            $product->datedProduct->increment('quantity', $product->quantity);
        }
    }

    public function addProduct(PurchasedProduct $product, int $quantity)
    {
        DB::transaction(function () use ($product, $quantity) {

            if(!$this->isUpdated()){
                $this->undo();
                $this->markAsUpdated();
            }
            if ($this->getPurchasedProductItems($product)->count() > 0) {
                throw new ProductAlreadyAddedException();
            }

            $this->validateStock($product, $quantity);

            $itemsData = $this->chooseItems($product, $quantity);

            $this->createItems($itemsData);
        });
        return $product;
    }

    public function removeProduct(PurchasedProduct $product)
    {
        if(!$this->isUpdated()){
            $this->undo();
            $this->markAsUpdated();
        }

        if ($this->getPurchasedProductItems($product)->count() == 0)
            throw new ProductNotAddedException();
        DB::transaction(function () use ($product) {
            $this->deletePurchasedProductItems($product);
            $this->load('orderedProducts');
            if ($this->orderedProducts->count() == 0) {
                $this->deletePrescriptions();
            }
        });
        return $product;
    }


    public function updateQuantity(PurchasedProduct $product, int $newQuantity)
    {

        if(!$this->isUpdated()){
            $this->undo();
            $this->markAsUpdated();
        }

        $this->deletePurchasedProductItems($product);
        $this->load('orderedProducts');
        $this->addProduct($product, $newQuantity);
        return $newQuantity;
    }

    public function reCheckout(string $address)
    {
        $customer = $this->customer;
        DB::transaction(function () use ($address, $customer) {

            $this->shipping_address = $address;

            $this->validateCheckout();

            $this->processStock();

            $this->processPayment();

            $this->markAsUnUpdated();

            Mail::to($customer)->send(new OrderUnderReview($this));
        });
    }

    protected function validateCheckout()
    {
        $customer = $this->customer;
        if ($this->shipping_address == null) throw new NullAddressException();
        if ($customer->money < $this->getTotal()) throw new NotEnoughMoneyException();
        $this->checkForProducts();
        $this->checkForPrescriptionProducts();
    }

    public function storePrescriptions($files)
    {
        if(!$this->isUpdated()){
            $this->undoPayment();
            $this->undoStock();
            $this->markAsUpdated();
        }
        $fileNames = [];
        foreach ($files as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $noSpaceOriginalName = str_replace(' ', '', $originalName);
            $filename = "{$noSpaceOriginalName}-{$this->id}.{$file->getClientOriginalExtension()}";
            $fileNames[] = $filename;
            Storage::disk('local')->put($filename, File::get($file));
            $prescription = ['prescription' => $filename];
            $this->prescriptions()->create($prescription);
        }

        return $fileNames;
    }

    public function deletePrescriptions()
    {
        if(!$this->isUpdated()){
            $this->undoPayment();
            $this->undoStock();
            $this->markAsUpdated();
        }
        $prescriptions = $this->prescriptions;
        foreach ($prescriptions as $prescription) {
            Storage::disk('local')->delete($prescription->prescription);
            $prescription->delete();
        }
        return $prescriptions->pluck('prescription');
    }

    protected function processPayment()
    {
        $customer = $this->customer;
        DB::transaction(function () use ($customer) {
            $total = $this->getTotal();
            $customer->decrement('money', $total);
            $pharmacy = Pharmacy::first();
            $pharmacy->increment('money', $total);
        });
    }

    public function storeShippingAddress(string $address)
    {
        if(!$this->isUpdated()){
            $this->undo();
            $this->markAsUpdated();
        }
        $this->shipping_address = $address;
        $this->save();
        return $this->address;
    }

    public function dispatch()
    {
        $this->claculateDeliveryFees();
        $this->determineDeliveryDate();
        $this->markAsDispatched();
        Mail::to($this->customer)->send(new OrderShipped($this));
    }

    protected function markAsDispatched()
    {
        $this->status = 'Dispatched';
        $this->save();
    }

    protected function claculateDeliveryFees()
    {
        $this->delivery_fees = 20000;
        $this->save();
    }

    protected function determineDeliveryDate()
    {
        $this->delivery_date = Carbon::now()->addDay();
        $this->save();
    }

    protected function markAsRejected()
    {
        $this->status = 'Rejected';
        $this->save();
    }

    public function reject(string $reason)
    {
        $this->markAsRejected();
        Mail::to($this->customer)->send(new OrderRejected($this, $reason));
    }


}
