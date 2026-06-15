<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffPositionResource extends JsonResource
{
    use HasTranslatableFields;

    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/admin/*') || (($request->user()?->role?->name['en'] ?? null) === 'admin');

        return array_merge([
            'uuid' => $this->uuid,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], $this->mapTranslatable(['name', 'description'], $isAdmin));
    }
}
