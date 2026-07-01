<?php

declare(strict_types=1);

namespace App\Http\Resources\Cards;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'employment_type' => $this->employment_type?->value,
            'location' => $this->location,
            'salary_from' => $this->salary_from,
            'salary_to' => $this->salary_to,
            'cover_image' => $this->cover_image,
            'published_at' => $this->published_at,
        ];
    }
}
