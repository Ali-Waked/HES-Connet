<?php

declare(strict_types=1);

namespace App\Actions\Conversation;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class SendMessageAction
{
    public function execute(Conversation $conversation, User $user, string $message): Message
    {
        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $message,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }
}
