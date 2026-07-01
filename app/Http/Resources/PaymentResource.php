<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'donation_id' => $this->when($this->donation_id, $this->donation?->uuid),
            'provider' => $this->provider,
            'provider_payment_id' => $this->provider_payment_id,
            'transaction_ref' => $this->transaction_ref,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'status' => $this->status?->value,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
        ];
    }
}
