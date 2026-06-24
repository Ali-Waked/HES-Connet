<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowDoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->user?->getTranslations('name'),
            'user_uuid' => $this->user?->uuid,
            'specialization' => $this->getTranslations('specialization'),
            'bio' => $this->getTranslations('bio'),
            'avatar' => $this->user->avatar,
            'cover_image' => $this->user->cover_image,
            'facilities' => $this->whenLoaded('facilities', fn () => $this->facilities->map(fn ($facility) => [
                'uuid' => $facility->uuid,
                'name' => $facility->getTranslations('name'),
                'facility_type' => $facility->facility_type?->value,
                'position' => $facility->pivot->position,
            ])),
            'departments' => $this->whenLoaded('departments', fn () => $this->departments->map(fn ($dept) => [
                'uuid' => $dept->uuid,
                'name' => $dept->getTranslations('name'),
            ])),
            'is_head_doctor' => $this->whenLoaded('headFacilities', fn () => $this->headFacilities->isNotEmpty(), false),
            'head_facilities' => $this->whenLoaded('headFacilities', fn () => $this->headFacilities->map(fn ($facility) => [
                'uuid' => $facility->uuid,
                'name' => $facility->getTranslations('name'),
                'facility_type' => $facility->facility_type?->value,
            ])),
        ];
    }
}
