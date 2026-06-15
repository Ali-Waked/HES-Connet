<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->user->uuid,
            'full_name' => $this->user->name,
            'email' => $this->user->email,
            'gender' => $this->user->profile?->gender?->value,
            'date_of_birth' => $this->user->profile?->birth_date?->format('Y-m-d'),
            'status' => $this->status?->value,
        ];
    }
}
