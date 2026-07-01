<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GenderType;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read int $id
 * @property int $user_id
 * @property string|null $phone
 * @property GenderType|null $gender
 * @property string|null $birth_date
 * @property string|null $address
 * @property string|null $profile_image
 * @property string|null $cover_image
 * @property-read User $user
 */
#[Fillable(['user_id', 'phone', 'gender', 'birth_date', 'address', 'profile_image', 'cover_image'])]
class UserProfiles extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'gender' => GenderType::class,
            'birth_date' => 'date',
        ];
    }

    public function getProfileImageAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    public function getCoverImageAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
