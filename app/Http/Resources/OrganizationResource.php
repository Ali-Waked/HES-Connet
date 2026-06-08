<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    use HasTranslatableFields;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Example: Check if request is from an admin route or has admin permissions
        $isAdmin = $request->is('api/admin/*') || ($request->user()?->role?->name === 'admin');

        return array_merge([
            'id' => $this->id,
            'uuid' => $this->uuid,
            'type' => $this->type,
        ], $this->mapTranslatable(['name'], $isAdmin));
    }
}
