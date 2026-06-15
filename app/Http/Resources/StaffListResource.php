<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->user->uuid,
            'full_name' => $this->user->name,
            'email' => $this->user->email,
            'specialization' => $this->specialization,
            'years_of_experience' => $this->experience_years,
            'consultation_fee' => $this->consultation_fee,
            'status' => $this->status?->value,
            'position' => $this->whenLoaded('position', fn () => [
                'uuid' => $this->position->uuid,
                'name' => $this->position->getTranslations('name'),
            ]),
        ];
    }
}
