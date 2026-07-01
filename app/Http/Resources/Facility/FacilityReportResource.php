<?php

declare(strict_types=1);

namespace App\Http\Resources\Facility;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'overview' => $this->resource['overview'] ?? [],
            'charts' => $this->resource['charts'] ?? [],
            'tables' => $this->resource['tables'] ?? [],
            'filters_applied' => $this->resource['filters_applied'] ?? [],
        ];
    }
}
