<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    use HasTranslatableFields;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/admin/*') || ($request->user() && $request->user()->hasSystemRole('super_admin'));

        return array_merge([
            'id' => $this->id,
            'uuid' => $this->uuid,
            'type' => $this->type,
            'facilities' => FacilityResource::collection($this->whenLoaded('facilities')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], $this->mapTranslatable(['name', 'description'], $isAdmin));
    }
}
