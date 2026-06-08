<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable(['title', 'content', 'is_active'])]
class Announcement extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'content'];
}
