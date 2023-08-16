<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'payerId' => $this->payer_id,
            'employee_id' => $this->employee_id,
            'amount' => $this->amount,
            'date' => $this->updated_at->format('Y-m-d'),
            'time' => $this->updated_at->format('g:i A')
        ];
    }
}
