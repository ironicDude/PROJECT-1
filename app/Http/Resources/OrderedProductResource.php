<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderedProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->datedProduct->purchasedProduct->product->name,
            'order_id' => $this->order_id,
            'id' => $this->id,
            'product_id' => $this->datedProduct->product_id,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
        ];
    }
}
