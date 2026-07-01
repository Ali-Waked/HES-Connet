<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\JobPost;
use Illuminate\Foundation\Events\Dispatchable;

class JobPosted
{
    use Dispatchable;

    public function __construct(
        public readonly JobPost $jobPost,
    ) {}
}
