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
            'reason' => $this->reason,
            'notes' => $this->notes,
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'facility_staff' => $this->whenLoaded('facilityStaff', fn () => [
                'uuid' => $this->facilityStaff->uuid,
                'staff' => $this->facilityStaff->relationLoaded('staff') ? new StaffListResource($this->facilityStaff->staff) : null,
                'facility' => $this->facilityStaff->relationLoaded('facility') ? new FacilityResource($this->facilityStaff->facility) : null,
            ]),
            'patient' => $this->whenLoaded('patient', fn () => [
                'uuid' => $this->patient->uuid,
                'full_name' => $this->patient->user?->getTranslations('name'),
                'email' => $this->patient->user?->email,
            ]),
            'doctor' => $this->whenLoaded('facilityStaff.staff', fn () => [
                'uuid' => $this->facilityStaff->staff->uuid,
                'full_name' => $this->facilityStaff->staff->user?->getTranslations('name'),
                'specialization' => $this->facilityStaff->staff->getTranslations('specialization'),
            ]),
            'facility' => $this->whenLoaded('facilityStaff.facility', fn () => [
                'uuid' => $this->facilityStaff->facility->uuid,
                'name' => $this->facilityStaff->facility->getTranslations('name'),
            ]),
            'reschedules' => AppointmentRescheduleResource::collection($this->whenLoaded('reschedules')),
            'prescriptions' => PrescriptionResource::collection($this->whenLoaded('prescriptions')),
        ];
    }
}
