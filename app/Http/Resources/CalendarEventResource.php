<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'type' => $this->resource['type'],
            'facility' => [
                'uuid' => $this->resource['facility_uuid'],
                'name' => $this->resource['facility_name'],
            ],
            'title' => $this->resource['title'],
            'start' => $this->resource['start'],
            'end' => $this->resource['end'],
            'color' => $this->resource['color'],
            ...$this->when($this->resource['type'] === 'schedule', [
                'day_of_week' => $this->resource['day_of_week'],
            ]),
            ...$this->when($this->resource['type'] === 'unavailability', [
                'reason' => $this->resource['reason'],
                'status' => $this->resource['status'],
            ]),
        ];
    }
}
