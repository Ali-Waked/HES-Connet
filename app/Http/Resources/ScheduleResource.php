<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'slot_duration' => $this->slot_duration,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'facility_staff' => $this->whenLoaded('facilityStaff', fn () => [
                'uuid' => $this->facilityStaff->uuid,
                'staff' => $this->facilityStaff->whenLoaded('staff', fn () => [
                    'uuid' => $this->facilityStaff->staff->uuid,
                    'name' => $this->facilityStaff->staff->user?->getTranslations('name'),
                    'specialization' => $this->facilityStaff->staff->getTranslations('specialization'),
                ]),
                'facility' => $this->facilityStaff->whenLoaded('facility', fn () => [
                    'uuid' => $this->facilityStaff->facility->uuid,
                    'name' => $this->facilityStaff->facility->getTranslations('name'),
                ]),
            ]),
        ];
    }
}
