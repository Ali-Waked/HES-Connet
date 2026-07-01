<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Category;
use Illuminate\Foundation\Events\Dispatchable;

class CategoryDeleted
{
    use Dispatchable;

    public function __construct(
        public readonly Category $category,
    ) {}
}
