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
            'about_preview' => $this->resource['about_preview'],
        ];
    }
}
