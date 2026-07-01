<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Story;
use Illuminate\Foundation\Events\Dispatchable;

class StoryCreated
{
    use Dispatchable;

    public function __construct(
        public readonly Story $story,
    ) {}
}
