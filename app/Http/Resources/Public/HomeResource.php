<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'statistics' => $this->resource['statistics'],
            'featured_facilities' => $this->resource['featured_facilities'],
            'facility_categories' => $this->resource['facility_categories'],
            'latest_articles' => $this->resource['latest_articles'],
            'latest_jobs' => $this->resource['latest_jobs'],
            'platform_reviews' => $this->resource['platform_reviews'],
            'latest_stories' => $this->resource['latest_stories'],
            'about_preview' => $this->resource['about_preview'],
        ];
    }
}
