<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\Patient\UpdateProfileRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function __construct(private readonly UuidResolver $uuid_resolver) {}

    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->with(['systemRoles', 'profile', 'staff'])
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make('password');

        $roles = [];
        if (! empty($data['roles'])) {
            $roles = collect($data['roles'])->map(function (string $uuid) {
                return $this->uuid_resolver->resolve(Role::class, $uuid);
            })->toArray();
            unset($data['roles']);
        }

        $user = User::create($data);

        if (! empty($roles)) {
            $user->systemRoles()->sync($roles);
        }

        return $user->load(['systemRoles', 'profile', 'staff']);
    }

    public function show(User $user): User
    {
        return $user->load(['systemRoles', 'profile', 'staff']);
    }

    public function update(User $user, array $data): User
    {
        if (! empty($data['roles'])) {
            $roles = collect($data['roles'])->map(function (string $uuid) {
                return $this->uuid_resolver->resolve(Role::class, $uuid);
            })->toArray();
            $user->systemRoles()->sync($roles);
            unset($data['roles']);
        }

        $user->update($data);

        return $user->refresh()->load(['systemRoles', 'profile', 'staff']);
    }

    public function destroy(User $user): void
    {
        $user->delete();
    }

    public function updateProfile(User $user, array $data, UpdateProfileRequest $request): User
    {
        if (isset($data['name'])) {
            $data['name'] = [
                'en' => $data['name']['en'] ?? null,
                'ar' => $data['name']['ar'] ?? null,
            ];
        }

        if ($request->has('city_id')) {
            $data['city_id'] = $this->uuid_resolver->resolve(User::class, $data['city_id']);
        }

        $userFields = array_intersect_key($data, array_flip(['name', 'email', 'city_id', 'locale', 'is_active', 'timezone']));
        $profileFields = array_intersect_key($data, array_flip(['phone', 'gender', 'birth_date', 'address']));

        if ($request->hasFile('avatar')) {
            $oldImage = $user->profile?->getRawOriginal('profile_image');
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            $profileFields['profile_image'] = $this->uploadFile($request->file('avatar'), 'users/avatars');
        }

        if ($request->hasFile('cover_image')) {
            $oldImage = $user->profile?->getRawOriginal('cover_image');
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            $profileFields['cover_image'] = $this->uploadFile($request->file('cover_image'), 'users/covers');
        }

        if (! empty($userFields)) {
            $user->fill($userFields);
            $user->save();
        }

        if (! empty($profileFields)) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }

        return $user->fresh([
            'systemRoles.permissions',
            'city',
            'staff',
            'patientProfile',
            'profile',
        ]);
    }

    protected function uploadFile(UploadedFile $file, string $path): string
    {
        return $file->store($path, 'public');
    }
}
