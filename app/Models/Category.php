<?php

namespace App\Models;

use App\Enums\CategoriesType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Fillable(['name','type'])]
class Category extends Model
{
    protected function casts(): array
    {
        return ['type' => CategoriesType::class];
    }

    public function articles():HasMany {
        return $this->hasMany(Article::class);
    }
}
