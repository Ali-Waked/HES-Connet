<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Provider;
use App\Models\OrganizationUser;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;
use Laravel\Sanctum\HasApiTokens;

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
 * @property int $role_id
 * @property-read Role|null $role
 * @property-read UserProfiles|null $profile
 */
#[Fillable(['name', 'email', 'password', 'role_id', 'provider', 'provider_id', 'last_seen_at', 'city_id','avatar','cover_image'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    use HasTranslations;
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfiles::class);
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

    public function hasRole(string|array $roles): bool
    {
        $roleName = $this->role?->name['en'] ?? null;

        return in_array($roleName, (array) $roles);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role?->permissions()->where('key', $permission)->exists() ?? false;
    }

    public function allPermissions(): Collection
    {
        return $this->role?->permissions ?? collect();
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
