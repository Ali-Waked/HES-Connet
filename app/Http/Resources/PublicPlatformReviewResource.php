<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicPlatformReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'admin_reply' => $this->admin_reply,
            'is_featured' => $this->is_featured,
            'user' => [
                'uuid' => $this->user->uuid,
                'name' => $this->user->getTranslations('name'),
                'avatar' => $this->user->avatar,
                'role' => $this->user->patient ? 'patient' : ($this->user->staff ? 'staff' : 'user'),
            ],
        ];
    }
}
