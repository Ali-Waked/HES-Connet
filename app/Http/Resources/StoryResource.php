<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->getTranslations('title'),
            'content' => $this->getTranslations('content'),
            'cover_image' => $this->cover_image,
            'status' => $this->status?->value,
            'is_fundraising' => $this->is_fundraising,
            'target_amount' => $this->target_amount,
            'collected_amount' => $this->collected_amount,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->uuid,
                'name' => $this->category->getTranslations('name'),
                'type' => $this->category->type,
            ]),
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient->user->uuid,
                'name' => $this->patient->user->getTranslations('name'),
                'avatar' => $this->patient->user->avatar,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_favorited' => $this->when($request->user(), fn () => $request->user()->hasFavorited($this->resource), false),
        ];
    }
}
