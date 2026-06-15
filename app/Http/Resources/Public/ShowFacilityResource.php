<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\FacilityStaff;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ShowFacilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'facility_type' => $this->facility_type?->value,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'cover_image' => $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null,
            'organization' => $this->whenLoaded('organization', fn () => [
                'uuid' => $this->organization->uuid,
                'name' => $this->organization->getTranslations('name'),
                'type' => $this->organization->type,
            ]),
            'head_staff' => $this->whenLoaded('headStaff', fn () => [
                'uuid' => $this->headStaff->uuid,
                'name' => $this->headStaff->user?->getTranslations('name'),
                'specialization' => $this->headStaff->getTranslations('specialization'),
            ]),
            'images' => $this->whenLoaded('facilityImages', fn () => $this->facilityImages->map(fn ($image) => [
                'id' => $image->id,
                'image_url' => $image->image_url ? Storage::disk('public')->url($image->image_url) : null,
            ])),
            'files' => $this->whenLoaded('facilityDocuments', fn () => $this->facilityDocuments->map(fn ($file) => [
                'id' => $file->id,
                'file_url' => $file->file_url ? Storage::disk('public')->url($file->file_url) : null,
                'document_type' => $file->document_type,
            ])),
            'doctors' => $this->whenLoaded('facilityStaff', fn () => $this->facilityStaff->map(fn (FacilityStaff $fs) => [
                'id' => $fs->staff->id,
                'uuid' => $fs->staff->uuid,
                'name' => $fs->staff->user?->getTranslations('name'),
                'specialization' => $fs->staff->getTranslations('specialization'),
                'position' => $fs->position,
            ])),
        ];
    }
}
