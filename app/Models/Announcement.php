<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable(['title', 'content', 'is_active'])]
#[Translatable(['title', 'content'])]
class Announcement extends Model
{
    use Auditable;
    use HasTranslations;
}
