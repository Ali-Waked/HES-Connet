<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'admin_reply' => $this->admin_reply,
            'replied_by' => $this->whenLoaded('repliedBy', fn () => [
                'id' => $this->repliedBy->id,
                'name' => $this->repliedBy->name,
            ]),
            'replied_at' => $this->replied_at,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->whenLoaded('user', fn () => [
                'uuid' => $this->user->uuid,
                'name' => $this->user->getTranslations('name'),
                'avatar' => $this->user->avatar,
                'email' => $this->user->email,
            ]),
        ];
    }
}
