<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'unsubscribe_token' => $this->unsubscribe_token,
            'email' => $this->email,
            'locale' => $this->locale,
            'is_active' => $this->is_active,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'types' => $this->subscriptionTypes->pluck('type')->toArray(),
        ];
    }
}
