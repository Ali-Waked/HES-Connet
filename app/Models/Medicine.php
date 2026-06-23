<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'description', 'image_url'])]
class Medicine extends Model
{
    use HasTranslations, HasUuids;

    public array $translatable = ['name', 'description'];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
        ];
    }

    public function getImageUrlAttribute(?string $value): ?string
    {
        return $value ? Storage::disk('public')->url($value) : null;
    }

    public function pharmacyMedicines(): HasMany
    {
        return $this->hasMany(PharmacyMedicine::class, 'medicine_id');
    }
}
