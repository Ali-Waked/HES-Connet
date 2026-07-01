<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    use HasTranslatableFields;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/admin/*') || ($request->user() && $request->user()->hasSystemRole('super_admin'));

        return array_merge([
            'id' => $this->id,
            'uuid' => $this->uuid,

            'head' => $this->whenLoaded('head', function () {
                return [
                    'uuid' => $this->head?->uuid,
                    'name' => $this->head?->staff?->user?->name,
                    'avatar' => $this->head?->staff?->user?->avatar,
                    'specialization' => $this->head?->staff?->specialization,
                ];
            }),

            'facility' => $this->whenLoaded('facilityStaff.facility', function () {
                return [
                    'uuid' => $this->facilityStaff?->facility?->uuid,
                    'name' => $this->facilityStaff?->facility?->name,
                ];
            }),

            'staff_count' => $this->whenCounted('staff'),

            'created_at' => $this->created_at,
            'is_active' => $this->is_active,
            'image' => $this->image,

        ], $this->mapTranslatable(['name'], $isAdmin));
    }
}
