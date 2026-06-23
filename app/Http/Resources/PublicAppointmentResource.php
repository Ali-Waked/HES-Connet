<?php

namespace App\Http\Resources;

use App\Enums\AppointmentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicAppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $status = AppointmentStatus::from($this->status);

        return [
            'id' => $this->uuid,

            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'is_active' => in_array($this->status, AppointmentStatus::activeStatuses()),
            ],

            'schedule' => [
                'start_at' => $this->start_at?->toDateTimeString(),
                'end_at' => $this->end_at?->toDateTimeString(),
                'date' => $this->start_at?->toDateString(),
                'time' => $this->start_at?->format('H:i'),
            ],

            'facility' => [
                'id' => $this->facilityStaff->facility?->uuid,
                'name' => $this->facilityStaff->facility?->name,
            ],

            'doctor' => [
                'id' => $this->facilityStaff?->staff?->uuid,
                'name' => $this->facilityStaff?->staff?->name,
                'avatar' => $this->facilityStaff?->staff?->user->avatar,
            ],

            'patient' => [
                'id' => $this->patient?->id,
            ],

            'reason' => $this->reason,

            'notes' => $this->notes,

            'cancellation_reason' => $this->cancellation_reason,

            'review' => $this->whenLoaded('review', function () {
                return [
                    'rating' => $this->review->rating,
                    'comment' => $this->review->comment,
                    'created_at' => $this->review->created_at?->toDateTimeString(),
                ];
            }),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
