<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
            'participants' => $this->whenLoaded('participants', function () use ($request) {
                return $this->participants
                    ->reject(fn ($user) => $user->id === $request->user()->id)
                    ->values()
                    ->map(fn ($user) => [
                        'uuid' => $user->uuid,
                        'name' => $user->getTranslations('name'),
                        'avatar' => $user->avatar,
                        'last_seen_at' => $user->last_seen_at,
                        'is_online' => $user->last_seen_at?->gt(now()->subMinutes(2)),
                        'last_read_at' => $user->pivot->last_read_at,
                    ]);
            }),
            'last_message' => new MessageResource($this->whenLoaded('lastMessage')),
            'unread_messages_count' => $this->unread_messages_count ?? 0,
        ];
    }
}
