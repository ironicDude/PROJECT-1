<?php

namespace App\Http\Resources\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatedProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->purchasedProduct->product->name,
            'purchaseId' => $this->purchase_id,
            'id' => $this->id,
            'productId' => $this->product_id,
            'quantity' => $this->quantity,
            'cost' => $this->purchase_price,
            'expiryDate' => $this->expiry_date,
            'manufacturingDate' => $this->manufacturing_date
        ];
    }
}
