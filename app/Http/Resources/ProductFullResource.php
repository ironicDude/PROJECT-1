<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductFullResource extends JsonResource
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
            'labeller' => $this->labeller,
            'dosage_form' => $this->dosage_form,
            'strength' => $this->strength,
            'otc' => $this->otc,
            'price' => ($this->isPurchased() && $this->purchasedProduct) ? $this->purchasedProduct->price : null,
            'availability' => $this->isPurchased() && $this->purchasedProduct &&$this->purchasedProduct->isAvailable(),
            'route' => $this->route,
            'generic' => $this->generic,
            'drug' => $this->drug->toArray(),
            'affected organisms' => $this->drug->affectedOrganisms->pluck('organism'),
            'categories' => $this->drug->categories->pluck('name'),
            'external_identifiers'=> $this->drug->externalIdentifiers->pluck('url'),
            'synonyms' => $this->drug->synonyms->pluck('synonym'),
    ];
    }
}
