<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'description', 'image_url'])]
class Medicine extends Model
{
    use HasUuids, HasTranslations;

    public array $translatable = ['name', 'description'];
}
