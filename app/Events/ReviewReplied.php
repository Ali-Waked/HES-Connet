<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\ReviewReply;
use Illuminate\Foundation\Events\Dispatchable;

class ReviewReplied
{
    use Dispatchable;

    public function __construct(
        public readonly ReviewReply $reviewReply,
    ) {}
}
