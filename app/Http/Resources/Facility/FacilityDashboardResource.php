<?php

declare(strict_types=1);

namespace App\Http\Resources\Facility;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cards' => $this->resource['cards'] ?? [],
            'growth_percentages' => $this->resource['growth_percentages'] ?? [],
            'recent_data' => $this->resource['recent_data'] ?? [],
            'charts' => $this->resource['charts'] ?? [],
        ];
    }
}
