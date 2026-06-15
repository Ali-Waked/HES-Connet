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
            'notes' => $this->notes,
            'staff' => $this->whenLoaded('staff', fn () => [
                'uuid' => $this->staff->uuid,
                'name' => $this->staff->user->getTranslations('name'),
            ]),
            'patient' => $this->whenLoaded('patient', fn () => [
                'uuid' => $this->patient->uuid,
                'name' => $this->patient->user->getTranslations('name'),
            ]),
        ];
    }
}
