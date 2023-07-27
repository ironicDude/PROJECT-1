<?php

namespace App\Http\Resources;

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
            'status' => $this->status->name,
            'total' => $this->total,
            'shipping_fees' => $this->shipping_fees,
            'shipping_address' => $this->shipping_address,
            'method' => $this->method->name,
            'delivery_date' => $this->delivery_date,
            'quantity' => $this->quantity,
            'products' => new OrderedProductCollection($this->orderedProducts),
            'prescriptions' => new PrescriptionCollection($this->prescriptions),

        ];
    }
}
