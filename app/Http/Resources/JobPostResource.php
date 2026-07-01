<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->content,
            'facility' => new FacilityResource($this->whenLoaded('facility')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'employment_type' => $this->employment_type?->value,
            'experience_level' => $this->experience_level?->value,
            'location' => $this->location,
            'salary' => [
                'from' => $this->salary_from,
                'to' => $this->salary_to,
                'currency' => $this->salary_currency,
            ],
            'salary_visible' => $this->is_salary_visible,
            'vacancies' => $this->vacancies,
            'featured' => $this->featured,
            'cover_image' => $this->cover_image,
            'views' => $this->views,
            'status' => $this->status?->value,
            'apply_method' => $this->apply_method?->value,
            'apply_value' => $this->apply_value,
            'published_at' => $this->published_at,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'is_favorited' => $this->when($request->user(), fn () => $request->user()->hasFavorited($this->resource), false),
        ];
    }
}
