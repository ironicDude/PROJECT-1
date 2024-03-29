<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'date' => $this->updated_at->format('Y-m-d'),
            'time' => $this->updated_at->format('g:i A'),
            'customer_id' => $this->customer_id,
            'employee_id' => $this->employee_id,
            'status' => $this->status,
            'total' => $this->getTotal(),
            'shipping_fees' => $this->shipping_fees,
            'shipping_address' => $this->shipping_address,
            'method' => $this->method,
            'delivery_date' => $this->delivery_date,
            'quantity' => $this->getQuantity(),
            'products' => new OrderedProductCollection($this->orderedProducts),
            'prescriptions' => $this->viewPrescriptions(),
        ];
    }
}
