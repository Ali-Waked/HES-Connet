<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentRescheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'old_start_at' => $this->old_start_at,
            'old_end_at' => $this->old_end_at,
            'new_start_at' => $this->new_start_at,
            'new_end_at' => $this->new_end_at,
            'reason' => $this->reason,
            'created_at' => $this->created_at,
        ];
    }
}
