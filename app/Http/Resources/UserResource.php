<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
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

            'system_roles' => $this->whenLoaded(
                'systemRoles',
                fn () => $this->systemRoles->map(fn ($role) => [
                    'uuid' => $role->uuid,
                    'name' => $role->getTranslations('name'),
                    'slug' => $role->slug,
                ])
            ),

            'system_permissions' => $this->whenLoaded(
                'systemRoles',
                fn () => $this->getSystemPermissions()->pluck('key')
            ),

            'staff_memberships' => $this->whenLoaded(
                'staff',
                fn () => $this->getAvailableWorkspaces()
            ),

            'dashboard_route' => $this->dashboard_route,

            'profile' => new UserProfilesResource(
                $this->whenLoaded('profile')
            ),

            'city' => new CityResource(
                $this->whenLoaded('city')
            ),
        ];
    }
}
