<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityPrescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'notes' => $this->notes,

            'created_at' => $this->created_at,

            'appointment' => [
                'uuid' => $this->appointment?->uuid,
                'start_at' => $this->appointment?->start_at,
                'end_at' => $this->appointment?->end_at,

                'patient' => [
                    'uuid' => $this->appointment?->patient?->uuid,
                    'name' => $this->appointment?->patient?->user?->name,
                    'phone' => $this->appointment?->patient?->user?->profile?->phone,
                    'avatar' => $this->appointment?->patient?->user?->avatar,
                ],

                'doctor' => [
                    'uuid' => $this->appointment?->facilityStaff?->staff?->uuid,
                    'name' => $this->appointment?->facilityStaff?->staff?->user?->name,
                    'avatar' => $this->appointment?->facilityStaff?->staff?->user?->avatar,
                ],
            ],

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'uuid' => $item->uuid,
                        'dose' => $item->dose ?? null,
                        'duration' => $item->duration ?? null,

                        'medicine' => [
                            'uuid' => $item->medicine?->uuid,
                            'name' => $item->medicine?->name,
                            'image_url' => $item->medicine?->image_url,
                        ],
                    ];
                });
            }),
        ];
    }
}
