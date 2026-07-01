<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Article;
use Illuminate\Foundation\Events\Dispatchable;

class ArticleCreated
{
    use Dispatchable;

    public function __construct(
        public readonly Article $article,
    ) {}
}
