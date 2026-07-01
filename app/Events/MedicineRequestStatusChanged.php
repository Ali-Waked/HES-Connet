<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\MedicationRequest;
use Illuminate\Foundation\Events\Dispatchable;

class MedicineRequestStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly MedicationRequest $medicationRequest,
    ) {}
}
