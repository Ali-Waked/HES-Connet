<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'notes' => $this->notes,
            'dispensed_at' => $this->dispensed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'patient' => $this->whenLoaded('patient.user', fn () => [
                'uuid' => $this->patient->uuid,
                'name' => $this->patient->user->name,
                'avatar' => $this->patient->user->avatar,
            ]),
            'pharmacy' => $this->whenLoaded('facility', fn () => [
                'uuid' => $this->facility->uuid,
                'name' => $this->facility->getTranslations('name'),
            ]),
            'prescription' => $this->whenLoaded('prescription', fn () => [
                'uuid' => $this->prescription->uuid,
                'status' => $this->prescription->status?->value,
                'notes' => $this->prescription->notes,
            ]),
            'pharmacist' => $this->whenLoaded('pharmacist.user', fn () => [
                'uuid' => $this->pharmacist->uuid,
                'name' => $this->pharmacist->user->name,
            ]),
        ];
    }
}
