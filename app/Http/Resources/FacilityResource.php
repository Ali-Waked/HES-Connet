<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
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
            'facility_type' => $this->facility_type,
            'status' => $this->status->value,
            'approval_status' => $this->approval_status,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'cover_image' => $this->cover_image,
            'is_featured' => $this->is_featured,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'owner' => $this->whenLoaded('owner', fn () => [
                'uuid' => $this->owner->uuid,
                'name' => $this->owner->getTranslations('name'),
                'email' => $this->owner->email,
            ]),

            'created_by' => $this->whenLoaded('createdBy', fn () => [
                'uuid' => $this->createdBy->uuid,
                'name' => $this->createdBy->getTranslations('name'),
                'email' => $this->createdBy->email,
            ]),
            'city' => $this->whenLoaded('city', fn () => [
                'uuid' => $this->city->uuid,
                'name' => $this->city->getTranslations('name'),
            ]),
            'parent' => new FacilityResource($this->whenLoaded('parent')),
            'children' => FacilityResource::collection($this->whenLoaded('children')),
            'images' => FacilityImageResource::collection($this->whenLoaded('facilityImages')),
            'files' => FacilityDocumentResource::collection($this->whenLoaded('facilityDocuments')),
            'departments' => $this->whenLoaded(
                'departments',
                fn () => $this->departments->map(fn ($department) => [
                    'uuid' => $department->uuid,
                    'name' => $department->getTranslations('name'),
                    'staff_count' => $department->staff_count,
                    'created_at' => $department->created_at,
                    'is_active' => $department->is_active,
                    'image' => $department->image,
                ])
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], $this->mapTranslatable(['name'], $isAdmin));
    }
}
