<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DonationMade
{
    use Dispatchable;

    public function __construct(
        public readonly string $donorName,
        public readonly float $amount,
        public readonly ?string $campaign = null,
    ) {}
}
