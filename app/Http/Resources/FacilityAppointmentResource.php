<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityAppointmentResource extends JsonResource
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
            'uuid' => $this->uuid,

            'scheduled_at' => $this->start_at,
            'status' => $this->status,
            // 'start_at' =>
            'patient' => [
                'id' => $this->patient?->id,
                'uuid' => $this->patient?->uuid,
                'name' => $this->patient?->user?->name,
                'avatar' => $this->patient?->user?->avatar,
            ],

            'facility' => [
                'uuid' => $this->facilityStaff?->facility?->uuid,
                'name' => $this->facilityStaff?->facility?->name,
            ],

            'staff' => [
                'id' => $this->facilityStaff?->id,
                'name' => $this->facilityStaff?->staff?->user?->name,
                'role' => $this->facilityStaff?->role?->slug,
                'avatar' => $this->facilityStaff?->staff?->user?->avatar,
            ],

            // 'prescriptions_count' => $this->prescriptions?->count(),

            'created_at' => $this->created_at,
        ];
    }
}
