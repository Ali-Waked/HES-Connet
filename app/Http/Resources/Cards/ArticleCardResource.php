<?php

declare(strict_types=1);

namespace App\Http\Resources\Cards;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->getTranslations('title'),
            'cover_image' => $this->cover_image,
            'published_at' => $this->published_at,
            'views' => $this->views,
        ];
    }
}
