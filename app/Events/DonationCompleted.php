<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Donation;
use Illuminate\Foundation\Events\Dispatchable;

class DonationCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly Donation $donation,
    ) {}
}
