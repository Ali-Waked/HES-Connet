<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AiPrompted;
use App\Models\AiLog;

class LogAiPrompt
{
    public function handle(AiPrompted $event): void
    {
        AiLog::create([
            'user_id' => $event->userId,
            'agent' => $event->agent,
            'prompt' => $event->prompt,
            'response' => $event->response,
            'metadata' => $event->metadata,
        ]);
    }
}
