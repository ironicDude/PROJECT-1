<?php

namespace App\Http\Resources\Application;

use App\Http\Resources\ApplicantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'applicant' => $this->applicant->first_name . $this->applicant->last_name,
            'vacancy' => $this->vacancy->title,
            'status' => $this->status,
            'date' => $this->created_at->format('Y-m-d'),
            'time' => $this->created_at->format('g:i A'),
        ];
    }
}
