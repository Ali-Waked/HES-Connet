<?php

declare(strict_types=1);

namespace App\Actions\Conversation;

use App\Models\Conversation;
use App\Models\User;

class MarkConversationAsReadAction
{
    public function execute(Conversation $conversation, User $user): void
    {
        $conversation->participants()
            ->updateExistingPivot($user->id, [
                'last_read_at' => now(),
            ]);
    }
}
