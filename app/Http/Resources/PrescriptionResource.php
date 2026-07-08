<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $doctor = $this->appointment?->facilityStaff?->staff;
        $facility = $this->appointment?->facilityStaff?->facility;

        return [
            'uuid' => $this->uuid,
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'appointment' => $this->whenLoaded('appointment', fn () => [
                'uuid' => $this->appointment->uuid,
                'start_at' => $this->appointment->start_at,
                'end_at' => $this->appointment->end_at,
                'status' => $this->appointment->status?->value,
                'reason' => $this->appointment->reason,
                'notes' => $this->appointment->notes,
                'cancellation_reason' => $this->appointment->cancellation_reason,
            ]),
            'doctor' => $doctor ? [
                'uuid' => $doctor->uuid,
                'name' => $doctor->user->getTranslations('name'),
                'avatar' => $doctor->user->avatar,
            ] : null,
            'facility' => $facility ? [
                'uuid' => $facility->uuid,
                'name' => $facility->getTranslations('name'),
                'cover_image' => $facility->cover_image,
            ] : null,
            'patient' => $this->appointment?->patient?->user ? [
                'uuid' => $this->appointment->patient->uuid,
                'name' => $this->appointment->patient->user->getTranslations('name'),
                'avatar' => $this->appointment->patient->user->avatar,
            ] : null,
            'items' => PrescriptionItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
