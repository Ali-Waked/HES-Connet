<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Spatie\Translatable\HasTranslations;

#[Fillable(['user_id','specialization','experience_years','bio','consultation_fee'])]
class Staff extends Model
{
    use HasTranslations;

    public array $translatable = ['specialization', 'bio'];
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
