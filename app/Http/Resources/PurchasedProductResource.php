<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasedProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'product_id' => $this->id,
                'quantity' => $this->datedProducts->sum('quantity'),
                'price'=> $this->price,
                'order_limit' => $this->order_limit,
                'minimum_stock_level' => $this->minimum_stock_level,
        ];
    }
}
