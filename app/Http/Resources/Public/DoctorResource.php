<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        info(['uuid' => $this->uuid,
            'user_uuid' => $this->user->uuid]);

        return [
            'uuid' => $this->uuid,
            'user_uuid' => $this->user->uuid,
            'name' => $this->user?->getTranslations('name'),
            'specialization' => $this->getTranslations('specialization'),
            'avatar' => $this->user->avatar,
            'cover_image' => $this->user->cover_image,
            'bio' => $this->getTranslations('bio'),
            'facilities_count' => $this->whenCounted('facilities_count'),
            'primary_facility' => $this->whenLoaded('facilities', fn () => $this->facilities->first() ? [
                'uuid' => $this->facilities->first()->uuid,
                'name' => $this->facilities->first()->getTranslations('name'),
                'facility_type' => $this->facilities->first()->facility_type?->value,
            ] : null),
            'is_favorited' => $this->when($request->user(), fn () => $request->user()->hasFavorited($this->resource), false),
        ];
    }
}
