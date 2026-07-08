<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class BroadcastNotification
{
    use Dispatchable;

    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $actionUrl = null,
        public readonly ?string $entityUuid = null,
        public readonly ?array $audience = null,
    ) {}
}
