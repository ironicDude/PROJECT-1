<?php

namespace App\Http\Resources\Product;

use App\Models\PurchasedProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductOverviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'name' => $this->name,
                'id' => $this->id,
                'drugId' => $this->drug->id,
                'labeller' => $this->labeller,
                'dosageForm' => $this->dosage_form,
                'strength' => $this->strength,
                'otc' => $this->otc,
                'price' => ($this->isPurchased() && $this->purchasedProduct) ? $this->purchasedProduct->price : null,
                'availability' => $this->isPurchased() && $this->purchasedProduct &&$this->purchasedProduct->isAvailable(),
                'rating' => $this->getRating(),
                // 'route' => $this->route,
                // 'generic' => $this->generic,
                // 'drug' => $this->drug->name,
                // 'affected organisms' => $this->drug->affectedOrganisms,
                // 'categories' => $this->drug->categories,
                // 'dosages' => $this->drug->dosages,
                // 'external_identifiers'=> $this->drug->externalIdentifiers,
                // 'interactions' => $this->drug->interactions,
                // 'prices' => $this->drug->prices,
                // 'synonyms' => $this->drug->synonyms,
        ];
    }//end of toArray

}
