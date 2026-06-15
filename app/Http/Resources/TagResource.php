<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    use HasTranslatableFields;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,

            'name' => $this->getTranslations('name'),

            'description' => $this->description,

            'type' => $this->type,

            'is_active' => $this->is_active,

            'articles_count' => $this->whenCounted('articles'),

            'created_at' => $this->created_at,
        ];
    }
}
