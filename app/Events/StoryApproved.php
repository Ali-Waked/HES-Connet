<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Story;
use Illuminate\Foundation\Events\Dispatchable;

class StoryApproved
{
    use Dispatchable;

    public function __construct(
        public readonly Story $story,
    ) {}
}
