<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'product_name' => $this->name,
                // 'labeller' => $this->labeller,
                // 'dosage_form' => $this->dosage_form,
                // 'strength' => $this->strength,
                // 'route' => $this->route,
                // 'generic' => $this->generic,
                // 'otc' => $this->otc,
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
