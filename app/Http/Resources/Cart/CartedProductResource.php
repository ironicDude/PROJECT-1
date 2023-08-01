<?php

namespace App\Http\Resources\Cart;

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
            'productId' => $this->datedProduct->product_id,
            'cartId' => $this->cart_id,
            'price' => $this->datedProduct->purchasedProduct->price,
            'name' => $this->datedProduct->purchasedProduct->product->name,
            'subtotal' => $this->subtotal,
            'quantity' => $this->quantity,
            'otc' => $this->datedProduct->purchasedProduct->product->otc,
        ];
    }
}
