<?php

declare(strict_types=1);

namespace App\Http\Resources\Facility;

use App\Http\Resources\FacilityResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserProfilesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->whenLoaded('user') ?: $this),
            'facility' => new FacilityResource($this->whenLoaded('facility')),
            'staff' => $this->whenLoaded('staff', fn () => [
                'uuid' => $this->staff->uuid,
                'specialization' => $this->staff->getTranslations('specialization'),
                'experience_years' => $this->staff->experience_years,
                'consultation_fee' => $this->staff->consultation_fee,
                'bio' => $this->staff->getTranslations('bio'),
            ]),
        ];
    }
}
