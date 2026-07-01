<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Cards\ArticleCardResource;
use App\Http\Resources\Cards\DoctorCardResource;
use App\Http\Resources\Cards\FacilityCardResource;
use App\Http\Resources\Cards\JobPostCardResource;
use App\Http\Resources\Cards\StoryCardResource;
use App\Models\Article;
use App\Models\Facility;
use App\Models\JobPost;
use App\Models\Staff;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->favoritable_type,
            'created_at' => $this->created_at,

            'item' => $this->whenLoaded(
                'favoritable',
                fn () => $this->resolveItem($this->favoritable)
            ),
        ];
    }

    private function resolveItem($favoritable): JsonResource
    {
        return match ($favoritable::class) {
            Facility::class => new FacilityCardResource($favoritable),
            Staff::class => new DoctorCardResource($favoritable),
            Article::class => new ArticleCardResource($favoritable),
            JobPost::class => new JobPostCardResource($favoritable),
            Story::class => new StoryCardResource($favoritable),

            default => throw new \RuntimeException(
                'Unknown favoritable type: '.$favoritable::class
            ),
        };
    }
}
