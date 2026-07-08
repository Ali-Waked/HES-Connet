<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Specialization;
use Illuminate\Foundation\Events\Dispatchable;

class SpecializationUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly Specialization $specialization,
    ) {}
}
