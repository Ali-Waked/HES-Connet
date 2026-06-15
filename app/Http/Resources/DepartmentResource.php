<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    use HasTranslatableFields;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/admin/*') || (($request->user()?->role?->name['en'] ?? null) === 'admin');

        return array_merge([
            'id' => $this->id,
            'uuid' => $this->uuid,
            'facility' => new FacilityResource($this->whenLoaded('facility')),
            'head' => new StaffResource($this->whenLoaded('head')),
            'created_at' => $this->created_at,
            'is_active'=> $this->is_active,
            'image' => $this->image,
        ], $this->mapTranslatable(['name'], $isAdmin));
    }
}
