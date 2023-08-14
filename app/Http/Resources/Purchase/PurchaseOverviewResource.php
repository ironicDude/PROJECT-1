<?php

namespace App\Http\Resources\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOverviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'purchase_id' => $this->id,
            'quantity' => $this->getQuantity(),
            'total' => $this->getTotal(),
            'date' => $this->created_at->format('Y-m-d'),
        ];
    }
}
