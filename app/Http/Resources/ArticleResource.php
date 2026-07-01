<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->getTranslations('title'),
            'content' => $this->getTranslations('content'),
            'status' => $this->status?->value,
            'views' => $this->views,
            'cover_image' => $this->cover_image,
            'comments_count' => $this->whenCounted('comments'),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'author' => new UserResource($this->whenLoaded('author')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'published_at' => $this->published_at,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
