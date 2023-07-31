<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'type' => $this->type,
            'accountStatus' => $this->account_status,
            'gender' => $this->gender,
            'mobile' => $this->mobile,
            'dateOfBirth' => $this->date_of_birth,
            'datOfJoining' => $this->date_of_joining,
            'salary' => $this->salary,
            'personalEmail' => $this->personal_email,
        ];
    }
}
