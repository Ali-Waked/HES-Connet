<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Log::info('channels.php loaded');

Broadcast::channel('user.{id}', function ($user, $id) {
    return $user->uuid === $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {
    return true;
    if (! $user) {
        return false;
    }

    $conversation = Conversation::withCount(['participants as is_participant' => fn ($q) => $q->where('user_id', $user->id)])
        ->find($conversationId);

    if (! $conversation) {
        return false;
    }

    return $conversation->is_participant > 0;
});
