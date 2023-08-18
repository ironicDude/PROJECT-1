<?php

namespace App\Http\Resources\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'purchaseId' => $this->id,
            'date' => $this->updated_at->format('Y-m-d'),
            'time' => $this->updated_at->format('g:i A'),
            'employeeId' => $this->employee_id,
            'total' => $this->getTotal(),
            'quantity' => $this->getQuantity(),
            'shippingFees' => $this->shipping_fees,
            'products' => new DatedProductCollection($this->datedProducts),
        ];
    }
}
