<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffUnavailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'reason' => $this->reason,
            'facility_staff' => $this->whenLoaded('facilityStaff', fn () => [
                'uuid' => $this->facilityStaff->uuid,
                'staff' => new StaffListResource($this->facilityStaff->whenLoaded('staff')),
            ]),
        ];
    }
}
