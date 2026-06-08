<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'type' => $this->type,
        ], $this->mapTranslatable(['name'], $isAdmin));
    }
}
