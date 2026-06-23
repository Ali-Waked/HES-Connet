<?php

declare(strict_types=1);

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailableSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'start' => $this['start'],
            'end' => $this['end'],
            'start_at' => $this['start_at'],
            'end_at' => $this['end_at'],
        ];
    }
}
