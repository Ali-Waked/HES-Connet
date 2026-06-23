<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentCalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'patient' => [
                'name' => $this->patient?->user?->name,
            ],
            'doctor' => [
                'name' => $this->facilityStaff?->staff?->user?->name,
            ],
            'facility' => [
                'name' => $this->facilityStaff?->facility?->name,
            ],
            'status' => $this->status?->value,
            'start_at' => $this->start_at?->toDateTimeString(),
            'end_at' => $this->end_at?->toDateTimeString(),
        ];
    }
}
