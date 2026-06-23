<?php

declare(strict_types=1);

namespace App\Http\Resources\Staff;

use App\Enums\FacilityStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'address' => $this->city?->name,
            'is_active' => $this->status === FacilityStatus::ACTIVE,
        ];
    }
}
