<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorRecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'specialty' => $this->specialty,
            'department' => $this->department,
            'facility' => [
                'uuid' => $this->facilityUuid,
                'name' => $this->facilityName,
            ],
            'photo' => $this->photo,
            'experience_years' => $this->experienceYears,
            'is_available' => $this->isAvailable,
            'next_available_appointment' => $this->nextAvailableAppointment,
        ];
    }
}
