<?php

namespace App\Http\Resources\Order;

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
            'orderId' => $this->order_id,
            'id' => $this->id,
            'productId' => $this->datedProduct->product_id,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'labeller' => $this->datedProduct->purchasedProduct->product->labeller,
        ];
    }
}
