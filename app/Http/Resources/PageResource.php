<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    use HasTranslatableFields;

    public function toArray(Request $request): array
    {
        return array_merge([
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], $this->mapTranslatable(['title', 'content'], true));
    }
}
