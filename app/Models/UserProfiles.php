<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GenderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $user_id
 * @property string|null $phone
 * @property GenderType|null $gender
 * @property string|null $birth_date
 * @property string|null $address
 * @property string|null $profile_image
 * @property string|null $cover_image
 * @property-read \App\Models\User $user
 */
class UserProfiles extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'gender',
        'birth_date',
        'address',
        'profile_image',
        'cover_image',
    ];

    protected function casts(): array
    {
        return [
            'gender' => GenderType::class,
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
