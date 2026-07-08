<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Ai;

use App\Models\AiConversation;
use App\Models\AiMessage;

class MessageService
{
    public function saveUserMessage(AiConversation $conversation, string $content): AiMessage
    {
        return AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $content,
        ]);
    }

    public function saveAssistantMessage(AiConversation $conversation, string $content, array $tokenUsage = []): AiMessage
    {
        return AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $content,
            'token_usage' => $tokenUsage ?: null,
        ]);
    }

    public function saveToolMessage(AiConversation $conversation, string $toolName, array $arguments, mixed $result): AiMessage
    {
        return AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'tool',
            'content' => is_string($result) ? $result : json_encode($result),
            'tool_name' => $toolName,
            'tool_arguments' => $arguments,
            'tool_result' => is_array($result) ? $result : ['result' => $result],
        ]);
    }

    public function getContextMessages(AiConversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (AiMessage $msg) => match ($msg->role) {
                'tool' => [
                    'role' => 'tool',
                    'content' => $msg->content,
                    'tool_name' => $msg->tool_name,
                    'tool_call_id' => $msg->tool_name,
                ],
                'user' => [
                    'role' => 'user',
                    'content' => $msg->content,
                ],
                'assistant' => [
                    'role' => 'assistant',
                    'content' => $msg->content,
                ],
                default => [
                    'role' => $msg->role,
                    'content' => $msg->content,
                ],
            })
            ->values()
            ->toArray();
    }
}
