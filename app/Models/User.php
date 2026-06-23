<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Provider;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Translatable\HasTranslations;

/**
 * @property-read string $uuid
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property Provider $provider
 * @property string|null $provider_id
 * @property Carbon|null $last_seen_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property-read UserProfiles|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $systemRoles
 * @property-read Staff|null $staff
 * @property-read Patient|null $patientProfile
 * @property-read Facility|null $activeWorkspace
 */
#[Fillable(['name', 'email', 'password', 'provider', 'provider_id', 'last_seen_at', 'city_id', 'avatar', 'cover_image'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    use HasTranslations;

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'provider' => Provider::class,
            'last_seen_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'name' => 'array',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function systemRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfiles::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function patientProfile(): HasOne
    {
        return $this->hasOne(Patient::class, 'user_id');
    }

    public function activeWorkspace(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'active_workspace_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }

    public function platformReviews(): HasMany
    {
        return $this->hasMany(PlatformReview::class);
    }

    public function searchHistories(): HasMany
    {
        return $this->hasMany(SearchHistory::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'donor_id');
    }

    public function organizationUsers(): HasMany
    {
        return $this->hasMany(OrganizationUser::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function hasSystemRole(string|array $roles): bool
    {
        return $this->systemRoles()
            ->whereIn('slug', (array) $roles)
            ->exists();
    }

    public function hasSystemPermission(string $permission): bool
    {
        return $this->systemRoles()
            ->whereHas('permissions', fn ($q) => $q->where('key', $permission))
            ->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasSystemPermission($permission)) {
            return true;
        }

        $activeFs = $this->getActiveFacilityStaff();
        if ($activeFs && $activeFs->role) {
            return $activeFs->role->permissions()
                ->where('key', $permission)
                ->exists();
        }

        return false;
    }

    public function getSystemPermissions(): Collection
    {
        return $this->systemRoles()
            ->with('permissions')
            ->get()
            ->flatMap(fn (Role $role) => $role->permissions)
            ->unique('id');
    }

    public function getAllPermissions(): Collection
    {
        $systemPermissions = $this->getSystemPermissions();

        $activeFs = $this->getActiveFacilityStaff();

        $facilityPermissions = $activeFs && $activeFs->role
            ? $activeFs->role->permissions
            : collect();

        return $systemPermissions->merge($facilityPermissions)->unique('id');
    }

    public function getActiveFacilityStaff(): ?FacilityStaff
    {
        if (! $this->active_workspace_id) {
            return null;
        }

        $staff = $this->staff;

        if (! $staff) {
            return null;
        }

        return $staff->facilityStaff()
            ->where('facility_id', $this->active_workspace_id)
            ->whereNull('ended_at')
            ->first();
    }

    public function getAvailableWorkspaces(): Collection
    {
        $staff = $this->staff;

        if (! $staff) {
            return collect();
        }

        return $staff->facilityStaff()
            ->with([
                'facility',
                'role',
                'role.permissions',
            ])
            ->whereNull('ended_at')
            ->get()
            ->map(function (FacilityStaff $fs) {
                return [
                    'workspace_id' => $fs->id,

                    'facility' => [
                        'id' => $fs->facility->id,
                        'uuid' => $fs->facility->uuid,
                        'name' => $fs->facility->name,
                        'type' => $fs->facility->type,
                    ],

                    'role' => [
                        'id' => $fs->role->id,
                        'uuid' => $fs->role->uuid,
                        'name' => $fs->role->name,
                        'slug' => $fs->role->slug,
                    ],

                    'permissions' => $fs->role
                        ->permissions
                        ->pluck('key')
                        ->values(),
                ];
            });
    }

    public function getDashboardRouteAttribute(): string
    {
        if ($this->hasSystemRole('super_admin')) {
            return '/admin/dashboard';
        }

        $activeFs = $this->getActiveFacilityStaff();

        if ($activeFs && $activeFs->role?->slug === 'doctor') {
            return '/staff/dashboard';
        }

        if ($this->hasSystemRole('facility_owner')) {
            return '/facility/dashboard';
        }

        if ($this->patientProfile()->exists()) {
            return '/patient/dashboard';
        }

        return '/';
    }

    public function getCoverImageAttribute(?string $value): ?string
    {
        return $value ? Storage::disk('public')->url($value) : null;
    }

    public function getAvatarAttribute(?string $value): ?string
    {
        return $value ? Storage::disk('public')->url($value) : null;
    }
}
