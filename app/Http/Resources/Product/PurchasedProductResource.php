<?php

namespace App\Http\Resources\Product;

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
                'productId' => $this->id,
                'quantity' => $this->datedProducts->sum('quantity'),
                'price'=> $this->price,
                'orderLimit' => $this->order_limit,
                'minimumStockLevel' => $this->minimum_stock_level,
        ];
    }
}
