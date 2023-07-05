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
            'name' => $request->name,
            'labeller' => $request->labeller,
            'dosage_form' => $request->dosage_form,
            'strength' => $request->strength,
            'route' => $request->route,
            'generic' => $request->generic,
            'otc' => $request->otc,
            'drug' => $request->drug,
            'affected organisms' => $request->drug->affectedOrganisms,
            'categories' => $request->drug->categories,
            'dosages' => $request->drug->dosages,
            'external_identifiers'=> $request->drug->externalIdentifiers,
            'interactions' => $request->drug->interactions,
            'prices' => $request->drug->prices,
            'synonyms' => $request->drug->synonyms,
        ];
    }
}
