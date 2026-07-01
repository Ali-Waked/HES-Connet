<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\PlatformReview;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class PlatformReviewReplied
{
    use Dispatchable;

    public function __construct(
        public readonly PlatformReview $platformReview,
        public readonly string $adminReply,
        public readonly User $admin,
    ) {}
}
