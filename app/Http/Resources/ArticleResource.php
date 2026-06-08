<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    use HasTranslatableFields;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/admin/*') || ($request->user()?->role?->name === 'admin');

        return array_merge([
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status,
            'views' => $this->views,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ], $this->mapTranslatable(['title', 'content'], $isAdmin));
    }
}
