<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    use HasTranslatableFields;
    
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/admin/*') || ($request->user() && $request->user()->hasSystemRole('super_admin'));

        return array_merge([
            'uuid' => $this->uuid,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ], $this->mapTranslatable(['name','description'], $isAdmin));
    }
}
