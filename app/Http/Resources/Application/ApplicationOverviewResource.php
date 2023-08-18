<?php

namespace App\Http\Resources\Application;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationOverviewResource extends JsonResource
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
            'applicantFirstName' => $this->applicant->first_name,
            'applicantLastName' => $this->applicant->last_name,
            'vacancy' => $this->vacancy->title,
            'status' => $this->status,
            'date' => $this->created_at->format('Y-m-d'),
            'time' => $this->created_at->format('g:i A'),
        ];
    }
}
