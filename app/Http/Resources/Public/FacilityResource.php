<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'facility_type' => $this->facility_type?->value,
            'cover_image' => $this->cover_image,
            'organization' => $this->whenLoaded('organization', fn () => [
                'uuid' => $this->organization->uuid,
                'name' => $this->organization->getTranslations('name'),
                'type' => $this->organization->type,
            ]),
            // 'head_staff' => $this->whenLoaded('headStaff', fn () => [
            //     'uuid' => $this->headStaff->uuid,
            //     'name' => $this->headStaff->user?->getTranslations('name'),
            //     'specialization' => $this->headStaff->getTranslations('specialization'),
            // ]),
            'images' => $this->whenLoaded('facilityImages'),
            // 'doctors_count' => $this->whenCounted('doctors_count'),
            'departments_count' => $this->whenCounted('departments_count'),
            'is_favorited' => $this->when($request->user(), fn () => $request->user()->hasFavorited($this->resource), false),
        ];
    }
}
