<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FacilityPatientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,

            'name' => $this->user?->name,
            'email' => $this->user?->email,

            'phone' => $this->phone ?? null,

            'last_visit' => $this->appointments?->last()?->scheduled_at,

            'assigned_staff' => $this->appointments?->last()?->facilityStaff?->staff?->user?->name,

            'assigned_role' => $this->appointments?->last()?->facilityStaff?->role?->slug,
        ];
    }
}
