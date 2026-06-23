<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'appointment_date' => $this->start_at?->toDateString(),
            'start_at' => $this->start_at?->toDateTimeString(),
            'end_at' => $this->end_at?->toDateTimeString(),
            'status' => $this->status?->value,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
