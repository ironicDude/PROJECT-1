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
            'product_id' => $this->datedProduct->product_id,
            'cart_id' => $this->cart_id,
            'price' => $this->datedProduct->purchasedProduct->price,
            'name' => $this->datedProduct->purchasedProduct->product->name,
            'subtotal' => $this->subtotal,
            'quantity' => $this->quantity,
            'otc' => $this->datedProduct->purchasedProduct->product->otc,
        ];
    }
}
