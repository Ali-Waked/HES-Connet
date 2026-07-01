<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityStaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'staff_id' => $this->staff_id,
            'facility_id' => $this->facility_id,
            'symptoms' => SymptomResource::collection($this->whenLoaded('symptoms')),
            'joined_at' => $this->joined_at,
            'ended_at' => $this->ended_at,
        ];
    }
}
