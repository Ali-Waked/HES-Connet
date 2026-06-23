<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    use HasTranslatableFields;

    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/admin/*') || ($request->user() && $request->user()->hasSystemRole('super_admin'));

        return array_merge([
            'uuid' => $this->uuid,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ], $this->mapTranslatable(['name'], $isAdmin));
    }
}
