<?php

namespace App\Http\Resources\Application;

use App\Http\Resources\Application\ApplicationOverviewCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicantResource extends JsonResource
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
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'resume' => $this->getResume(),
            'address' => $this->address,
            'appliedAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'applications' => new ApplicationOverviewCollection($this->applications),
        ];
    }
}
