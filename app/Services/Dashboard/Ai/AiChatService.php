<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Ai;

use App\Ai\Agents\HESAssistant;
use App\Ai\Contracts\AiProvider;
use App\Ai\Providers\AnthropicProvider;
use App\Ai\Providers\OpenAiProvider;
use App\Events\AiPrompted;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    private readonly AiProvider $provider;

    public function __construct(
        private readonly ConversationService $conversationService,
        private readonly MessageService $messageService,
    ) {
        $driver = config('ai.default_provider', 'openai');

        $this->provider = match ($driver) {
            'anthropic' => app(AnthropicProvider::class),
            default => app(OpenAiProvider::class),
        };
    }

    public function ask(int $userId, ?string $conversationUuid, string $message): array
    {
        $language = $this->conversationService->detectLanguage($message);

        if ($conversationUuid === null) {
            $title = $this->conversationService->generateTitle($message, $language);

            $conversation = $this->conversationService->create(
                userId: $userId,
                language: $language,
                title: $title,
            );
        } else {
            $conversation = $this->conversationService->getByUuid($conversationUuid, $userId);
        }

        $userMessage = $this->messageService->saveUserMessage($conversation, $message);

        $agent = new HESAssistant;

        $tools = [];
        foreach ($agent->tools() as $tool) {
            $tools[$tool->name()] = $tool;
        }

        $contextMessages = $this->messageService->getContextMessages($conversation);

        $systemPrompt = $agent->instructions()."\n\n"
            ."IMPORTANT: Respond in the same language as the user's message. "
            .'If the user writes in Arabic, respond in Arabic. '
            .'If the user writes in English, respond in English. '
            .'Do not switch languages unless the user explicitly asks you to.';

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$contextMessages,
        ];

        try {
            $result = $this->provider->chatWithMessages($messages, $tools);
        } catch (\Throwable $e) {
            Log::error('AI chat failed', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id,
            ]);

            throw $e;
        }

        $toolsUsed = [];

        foreach ($result['tool_calls'] ?? [] as $call) {
            $functionName = $call['function']['name'] ?? $call['name'] ?? '';
            $arguments = isset($call['function']['arguments'])
                ? (is_string($call['function']['arguments']) ? json_decode($call['function']['arguments'], true) : $call['function']['arguments'])
                : ($call['input'] ?? []);

            $toolResult = null;
            foreach ($result['tool_results'] ?? [] as $tr) {
                if ($tr['tool'] === $functionName) {
                    $toolResult = $tr['result'];
                    break;
                }
            }

            $this->messageService->saveToolMessage(
                $conversation,
                $functionName,
                $arguments ?? [],
                $toolResult ?? [],
            );

            $toolsUsed[] = [
                'tool' => $functionName,
                'arguments' => $arguments,
            ];
        }

        $assistantMessage = $this->messageService->saveAssistantMessage(
            $conversation,
            $result['content'],
            tokenUsage: [
                'prompt_tokens' => $result['prompt_tokens'] ?? 0,
                'completion_tokens' => $result['completion_tokens'] ?? 0,
                'total_tokens' => $result['total_tokens'] ?? 0,
            ],
        );

        $this->conversationService->updateLastMessageAt($conversation);

        AiPrompted::dispatch(
            userId: $userId,
            agent: class_basename($agent),
            prompt: $message,
            response: $result['content'],
            metadata: [
                'conversation_uuid' => $conversation->uuid,
                'tool_calls' => $result['tool_calls'],
                'tool_results' => $result['tool_results'],
                'token_usage' => $result['prompt_tokens'] ?? 0,
            ],
        );

        return [
            'conversation' => $conversation->fresh(),
            'message' => $result['content'],
            'tools_used' => $toolsUsed,
            'language' => $conversation->language ?? $language,
        ];
    }
}
