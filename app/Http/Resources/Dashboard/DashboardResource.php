<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cards' => $this->resource['cards'] ?? [],
            'growth_percentages' => $this->resource['growth_percentages'] ?? [],
            'recent_activity' => $this->resource['recent_activity'] ?? [],
            'charts' => $this->resource['charts'] ?? [],
        ];
    }
}
