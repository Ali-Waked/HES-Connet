<?php

declare(strict_types=1);

namespace App\Actions\Conversation;

use App\Enums\ConversationType;
use App\Models\Conversation;
use App\Models\User;

class CreateConversationAction
{
    public function execute(User $user, array $data): Conversation
    {
        $conversation = Conversation::create([
            'type' => ConversationType::from($data['type']),
            'created_by' => $user->id,
        ]);

        $participantIds = collect($data['participant_ids'])
            ->push($user->uuid)
            ->unique()
            ->map(fn (string $uuid) => User::where('uuid', $uuid)->value('id'))
            ->filter();

        $conversation->participants()->attach($participantIds);

        $conversation->load('participants');

        return $conversation;
    }
}
