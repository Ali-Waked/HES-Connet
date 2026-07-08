<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\ContactMessage;
use Illuminate\Foundation\Events\Dispatchable;

class ContactMessageSubmitted
{
    use Dispatchable;

    public function __construct(
        public readonly ContactMessage $contactMessage,
    ) {}
}
