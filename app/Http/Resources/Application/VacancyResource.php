<?php

namespace App\Http\Resources\Application;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VacancyResource extends JsonResource
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
            'employeeId' => $this->employee_id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'salary' => $this->salary,
            'deadline' => $this->deadline,
            'numberOfVacancies' => $this->number_of_vacancies,
            'createdAtDate' => $this->created_at->format('Y-m-d'),
            'createdAtTime' => $this->created_at->format('g:i A'),
            'applications' => new ApplicationOverviewCollection($this->applications),
        ];
    }
}
