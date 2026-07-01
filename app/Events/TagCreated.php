<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Tag;
use Illuminate\Foundation\Events\Dispatchable;

class TagCreated
{
    use Dispatchable;

    public function __construct(
        public readonly Tag $tag,
    ) {}
}
