<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_visible' => $this->is_visible,
            'created_at' => $this->created_at,
            'facility' => $this->whenLoaded('facility', fn () => [
                'uuid' => $this->facility->uuid,
                'name' => $this->facility->getTranslations('name'),
            ]),
            'patient' => $this->whenLoaded('patient', fn () => [
                'uuid' => $this->patient->user->uuid,
                'name' => $this->patient->user->getTranslations('name'),
            ]),
        ];
    }
}
