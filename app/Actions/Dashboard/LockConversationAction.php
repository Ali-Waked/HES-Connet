<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\ConversationStatus;
use App\Models\Conversation;

class LockConversationAction
{
    public function execute(Conversation $conversation): Conversation
    {
        $conversation->update([
            'status' => ConversationStatus::LOCKED,
        ]);

        return $conversation;
    }
}
