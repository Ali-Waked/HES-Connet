<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'email' => $this->email,
            'provider' => $this->provider,
            'avatar' => $this->avatar,
            'cover_image' => $this->cover_image,
            'last_seen_at' => $this->last_seen_at,
            'role' => new RoleResource($this->whenLoaded('role')),
            'profile' => new UserProfilesResource($this->whenLoaded('profile')),
            'city' => new CityResource($this->whenLoaded('city'))
        ];
    }
}
