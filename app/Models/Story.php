<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable(['patient_id', 'content', 'status', 'is_fundraising', 'target_amount', 'collected_amount'])]
class Story extends Model
{
    use HasUuids, HasTranslations;

    public array $translatable = ['content'];
}
