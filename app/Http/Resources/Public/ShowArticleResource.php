<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Http\Resources\ArticleImageResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->getTranslations('title'),
            'content' => $this->getTranslations('content'),
            'cover_image' => $this->cover_image,
            'views' => $this->views,
            'published_at' => $this->published_at,
            'author' => $this->whenLoaded('author', fn () => [
                'uuid' => $this->author->uuid,
                'name' => $this->author->getTranslations('name'),
            ]),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'images' => ArticleImageResource::collection($this->whenLoaded('images')),
            'comments' => $this->whenLoaded('comments', fn () => $this->comments->map(fn ($comment) => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => [
                    'uuid' => $comment->user->uuid,
                    'name' => $comment->user->getTranslations('name'),
                ],
                'created_at' => $comment->created_at,
            ])),
        ];
    }
}
