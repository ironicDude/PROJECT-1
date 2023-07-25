<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartedProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->purchasedProduct->product_id,
            'cart_id' => $this->cart_id,
            'price' => $this->purchasedProduct->price,
            'name' => $this->purchasedProduct->product->name,
            'subtotal' => $this->subtotal,
            'quantity' => $this->quantity,
            'otc' => $this->purchasedProduct->product->otc,
        ];
    }
}
