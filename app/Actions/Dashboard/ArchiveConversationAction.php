<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\ConversationStatus;
use App\Models\Conversation;

class ArchiveConversationAction
{
    public function execute(Conversation $conversation): Conversation
    {
        $conversation->update([
            'status' => ConversationStatus::ARCHIVED,
        ]);

        return $conversation;
    }
}
