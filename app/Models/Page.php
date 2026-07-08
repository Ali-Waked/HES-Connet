<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PageStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable(['title', 'content'])]
#[Fillable(['uuid', 'slug', 'title', 'content', 'status'])]
class Page extends Model
{
    use Auditable;
    use HasTranslations, HasUuids;

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'content' => 'array',
            'status' => PageStatus::class,
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
