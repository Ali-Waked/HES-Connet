<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    use HasTranslatableFields;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/admin/*') || ($request->user()?->role?->name === 'admin');

        return array_merge([
            'id' => $this->id,
            'uuid' => $this->uuid,
            'experience_years' => $this->experience_years,
            'consultation_fee' => $this->consultation_fee,
            'user' => new UserResource($this->whenLoaded('user')),
        ], $this->mapTranslatable(['specialization', 'bio'], $isAdmin));
    }
}
