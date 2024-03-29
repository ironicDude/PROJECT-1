<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Initialize an empty array to store distinct purchased products with their corresponding quantities and subtotals.
        $distinctItems = [];

        foreach ($this->cartedProducts as $item) {
            // Get the PurchasedProduct object associated with the current item.
            $purchasedProduct = $item->datedProduct->purchasedProduct->product->name;

            // Check if the PurchasedProduct appears for the first time in the 'distinct' array.
            if (!isset($distinctItems[$purchasedProduct])) {
                // If the PurchasedProduct is not yet in the 'distinct' array, add it with the initial quantity and subtotal.
                $distinctItems[$purchasedProduct] = [
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'id' => $item->datedProduct->product_id,
                    'otc' => $item->datedProduct->purchasedProduct->product->otc,
                    'price' => $item->datedProduct->purchasedProduct->price,
                    'cartId' => $this->id
                ];
            } else {
                // If the PurchasedProduct is already in the 'distinct' array, increment the quantity and subtotal.
                $distinctItems[$purchasedProduct]['quantity'] += $item->quantity;
                $distinctItems[$purchasedProduct]['subtotal'] += $item->subtotal;
            }
        }
        return [
            'cartId' => $this->id,
            'items' => $distinctItems,
            'prescriptions' => $this->viewPrescriptions(),
            'delivery' => $this->delivery,
            'Subtotal' => $this->subtotal,
            'Total' => $this->getTotal(),
            'Quantity' => $this->getQuantity(),
        ];
    }
}
