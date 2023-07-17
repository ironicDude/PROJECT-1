<?php

namespace App\Http\Resources;

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
                'labeller' => $this->labeller,
                'dosage_form' => $this->dosage_form,
                'strength' => $this->strength,
                'otc' => $this->otc,
                'price' => $this->purchasedProducts->pluck('price'),
                'availability' => $this->purchasedProducts->isNotEmpty()
                    ? $this->purchasedProducts->map(function ($purchasedProduct) {
                        return $purchasedProduct->quantity > $purchasedProduct->minimum_stock_level;
                    })
                : [false],
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


    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @return array
     */
    public function with($request)
    {
    }
}
