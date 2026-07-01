<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\ProfessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable(['name', 'description'])]
#[Fillable(['uuid', 'name', 'slug', 'description', 'is_active'])]
class Profession extends Model
{
    /** @use HasFactory<ProfessionFactory> */
    use Auditable, HasFactory;

    use HasTranslations, HasUuids;

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
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

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
