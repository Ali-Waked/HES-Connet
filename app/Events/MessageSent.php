<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("conversation.{$this->message->conversation_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'message' => [
                'id' => $this->message->id,
                'sender_id' => $this->message->sender_id,
                'content' => $this->message->message,
                'sender' => [
                    'uuid' => $this->message->sender->uuid,
                    'name' => $this->message->sender->getTranslations('name'),
                    'avatar' => $this->message->sender->avatar,
                ],
                'created_at' => $this->message->created_at->toIso8601String(),
            ],
        ];
    }
}
