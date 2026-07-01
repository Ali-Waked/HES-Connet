<?php

declare(strict_types=1);

namespace App\Http\Resources\Cards;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->user?->getTranslations('name'),
            'avatar' => $this->user?->avatar,
            'cover_image' => $this->user?->cover_image,
            'specialization' => $this->getTranslations('specialization'),
        ];
    }
}
