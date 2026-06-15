<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'staff' => new StaffListResource($this->whenLoaded('staff')),
            'patient' => new PatientListResource($this->whenLoaded('patient')),
            'facility' => new FacilityResource($this->whenLoaded('facility')),
            'reschedules' => AppointmentRescheduleResource::collection($this->whenLoaded('reschedules')),
            'prescriptions' => PrescriptionResource::collection($this->whenLoaded('prescriptions')),
        ];
    }
}
