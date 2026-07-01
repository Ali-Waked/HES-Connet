<?php

declare(strict_types=1);

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'email' => $this->email,
            'locale' => $this->locale,
            'is_active' => $this->is_active,
            'is_verified' => $this->email_verified_at !== null,
            'provider' => $this->provider,
            'avatar' => $this->avatar,
            'cover_image' => $this->cover_image,
            'last_seen_at' => $this->last_seen_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'system_roles' => $this->whenLoaded('systemRoles', fn () => $this->systemRoles->map(fn ($role) => [
                'uuid' => $role->uuid,
                'name' => $role->getTranslations('name'),
                'slug' => $role->slug,
            ])),

            'system_permissions' => $this->whenLoaded('systemRoles', fn () => $this->getSystemPermissions()->pluck('key')),

            'profile' => $this->whenLoaded('profile', fn () => [
                'phone' => $this->profile->phone,
                'gender' => $this->profile->gender,
                'birth_date' => $this->profile->birth_date,
                'address' => $this->profile->address,
            ]),

            'city' => $this->whenLoaded('city', fn () => [
                'uuid' => $this->city->uuid,
                'name' => $this->city->name,
            ]),

            'staff_memberships' => $this->whenLoaded('staff', fn () => $this->getAvailableWorkspaces()),

            'dashboard_route' => $this->dashboard_route,
        ];
    }
}
