<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->getTranslations('title'),
            'cover_image' => $this->cover_image,
            'published_at' => $this->published_at,
            'views' => $this->views,
            'comments_count' => $this->whenCounted('comments'),
            'author' => $this->whenLoaded('author', fn () => [
                'uuid' => $this->author->uuid,
                'name' => $this->author->getTranslations('name'),
            ]),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'is_favorited' => $this->when($request->user(), fn () => $request->user()->hasFavorited($this->resource), false),
        ];
    }
}
