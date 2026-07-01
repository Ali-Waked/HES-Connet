<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AiPrompted
{
    use Dispatchable;

    public function __construct(
        public readonly ?int $userId,
        public readonly string $agent,
        public readonly string $prompt,
        public readonly ?string $response = null,
        public readonly ?array $metadata = null,
    ) {}
}
