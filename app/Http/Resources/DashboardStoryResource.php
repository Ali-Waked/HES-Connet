<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient->uuid,
                'name' => $this->patient->user?->getTranslations('name'),
                'avatar' => $this->patient->user?->avatar,
            ]),
            'cover_image' => $this->cover_image,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->uuid,
                'name' => $this->category->getTranslations('name'),
                'type' => $this->category->type,
            ]),
            'title' => $this->getTranslations('title'),
            'content' => $this->getTranslations('content'),
            'status' => $this->status?->value,
            'is_fundraising' => $this->is_fundraising,
            'target_amount' => $this->target_amount,
            'collected_amount' => $this->collected_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
