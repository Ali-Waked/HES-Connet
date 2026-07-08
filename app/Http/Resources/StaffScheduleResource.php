<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        info($this->day_of_week);

        return [
            'id' => $this->id,
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'slot_duration' => $this->slot_duration,
            'is_active' => $this->is_active,
            'facility_staff' => $this->whenLoaded('facilityStaff', fn () => [
                'uuid' => $this->facilityStaff->uuid,
                'staff' => $this->facilityStaff->relationLoaded('staff')
                    ? new StaffListResource($this->facilityStaff->staff)
                    : null,
                'facility' => $this->facilityStaff->relationLoaded('facility')
                    ? new FacilityResource($this->facilityStaff->facility)
                    : null,
            ]),
        ];
    }
}
