<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'customer_id' => $this->customer_id,
            'cart_id' => $this->id,
            'items' => new CartedProductCollection($this->cartedProducts),
            'delivery' => $this->delivery,
            'Subtotal' => $this->subtotal,
            'Total' => $this->total,
            'Quantity' => $this->quantity,
        ];
    }
}
