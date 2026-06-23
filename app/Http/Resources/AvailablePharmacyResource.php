<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailablePharmacyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        info($this->cover_image);

        return [
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'cover_image' => $this->cover_image,
            'available_items' => $this->available_items,
            'total_items' => $this->total_items,
            'coverage_percentage' => $this->coverage_percentage,
            'can_fulfill' => $this->can_fulfill,
            'total_price' => $this->total_price ?? 0,
        ];
    }
}
