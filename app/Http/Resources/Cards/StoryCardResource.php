<?php

declare(strict_types=1);

namespace App\Http\Resources\Cards;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->getTranslations('title'),
            'cover_image' => $this->cover_image,
            'status' => $this->status?->value,
            'is_fundraising' => $this->is_fundraising,
            'target_amount' => $this->target_amount,
        ];
    }
}
