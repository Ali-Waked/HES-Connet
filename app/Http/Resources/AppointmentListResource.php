<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'status' => $this->status?->value,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'facility_staff' => $this->whenLoaded('facilityStaff', fn () => [
                'uuid' => $this->facilityStaff->uuid,
                'staff' => $this->facilityStaff->whenLoaded('staff', fn () => [
                    'uuid' => $this->facilityStaff->staff->uuid,
                    'name' => $this->facilityStaff->staff->user?->getTranslations('name'),
                ]),
                'facility' => $this->facilityStaff->whenLoaded('facility', fn () => [
                    'uuid' => $this->facilityStaff->facility->uuid,
                    'name' => $this->facilityStaff->facility->getTranslations('name'),
                ]),
            ]),
            'patient' => $this->whenLoaded('patient', fn () => [
                'uuid' => $this->patient->uuid,
                'name' => $this->patient->user?->getTranslations('name'),
            ]),
        ];
    }
}
