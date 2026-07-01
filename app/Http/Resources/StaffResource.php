<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    use HasTranslatableFields;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'experience_years' => $this->experience_years,
            'consultation_fee' => $this->consultation_fee,
            'status' => $this->user?->last_seen_at,

            'user' => new UserResource(
                $this->whenLoaded('user')
            ),

            'facilities' => $this->whenLoaded(
                'facilities',
                fn () => $this->facilities->map(function ($facility) {
                    info($facility->pivot);

                    $position = $facility->pivot?->position;
                    $department = $facility->pivot?->department;
                    $role = $facility->pivot?->role;

                    return [
                        'uuid' => $facility->uuid,
                        'facility_staff_uuid' => $facility->pivot?->uuid,
                        'joined_at' => $facility->pivot->joined_at,
                        'name' => $facility->getTranslations('name'),

                        'position' => $position ? [
                            'uuid' => $position->uuid,
                            'name' => $position->getTranslations('name'),
                        ] : null,
                        'department' => $department ? [
                            'uuid' => $department->uuid,
                            'name' => $department->getTranslations('name'),
                        ] : null,
                        'role' => $role ? [
                            'id' => $role->id,
                            'name' => $role->getTranslations('name'),
                            'slug' => $role->slug,
                        ] : null,
                    ];
                })->values()
            ),

            'specialization' => $this->getTranslations('specialization'),

            'bio' => $this->getTranslations('bio'),
        ];
    }
}
