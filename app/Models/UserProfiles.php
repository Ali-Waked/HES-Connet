<?php

namespace App\Models;

use App\Enums\GenderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable(['user_id','phone','gender','birth_date','address','profile_image','cover_image'])]
class UserProfiles extends Model
{
    protected function casts():array
    {
        return [
            'gender' => GenderType::class,
        ];
    }
    public function user():BelongsTo {
        return $this->belongsTo(User::class);
    }
}
