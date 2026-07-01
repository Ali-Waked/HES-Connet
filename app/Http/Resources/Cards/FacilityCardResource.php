<?php

declare(strict_types=1);

namespace App\Http\Resources\Cards;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'cover_image' => $this->cover_image,
            'facility_type' => $this->facility_type,
            'is_featured' => $this->is_featured,
        ];
    }
}
