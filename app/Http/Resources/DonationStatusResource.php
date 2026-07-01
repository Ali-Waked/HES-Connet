<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => $this['status'] ?? 'unknown',
            'donation_status' => $this['donation_status'] ?? null,
            'payment_status' => $this['payment_status'] ?? null,
            'paid_at' => $this['paid_at'] ?? null,
        ];
    }
}
