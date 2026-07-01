<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Comment;
use Illuminate\Foundation\Events\Dispatchable;

class CommentAdded
{
    use Dispatchable;

    public function __construct(
        public readonly Comment $comment,
    ) {}
}
