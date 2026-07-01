<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'status' => $this->status?->value,
            'story' => $this->whenLoaded('story', fn () => [
                'id' => $this->story->uuid,
                'title' => $this->story->getTranslations('title'),
            ]),
            'donor' => $this->whenLoaded('donor', fn () => [
                'id' => $this->donor->uuid,
                'name' => $this->donor->name,
                'avatar' => $this->donor->avatar,
            ]),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at,
        ];
    }
}
