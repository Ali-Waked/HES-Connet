<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name'])]
class Symptom extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    public $timestamps = false;
}
