<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\HESAssistant;
use App\Ai\Agents\PatientHealthAssistant;
use App\Ai\Contracts\AiProvider;
use App\Ai\Providers\AnthropicProvider;
use App\Ai\Providers\OpenAiProvider;
use App\Events\AiPrompted;

class AiService
{
    private readonly AiProvider $provider;

    public function __construct()
    {
        $driver = config('ai.default_provider', 'openai');

        $this->provider = match ($driver) {
            'anthropic' => app(AnthropicProvider::class),
            default => app(OpenAiProvider::class),
        };
    }

    public function askAssistant(string $message, ?int $userId = null): string
    {
        $agent = new HESAssistant;

        $tools = [];
        foreach ($agent->tools() as $tool) {
            $tools[$tool->name()] = $tool;
        }

        $result = $this->provider->chatWithTools(
            $agent->instructions(),
            $message,
            $tools,
        );

        $response = $result['content'];

        AiPrompted::dispatch(
            userId: $userId,
            agent: class_basename($agent),
            prompt: $message,
            response: $response,
            metadata: ['tool_calls' => $result['tool_calls']],
        );

        return $response;
    }

    public function consultPatient(array $data, ?int $userId = null): array
    {
        $agent = new PatientHealthAssistant;

        $tools = [];
        foreach ($agent->tools() as $tool) {
            $tools[$tool->name()] = $tool;
        }

        $result = $this->provider->chatWithTools(
            $agent->instructions(),
            $data['message'],
            $tools,
        );

        $content = $result['content'];

        $parsed = $this->parseStructuredResponse($content);

        AiPrompted::dispatch(
            userId: $userId,
            agent: class_basename($agent),
            prompt: $data['message'],
            response: $content,
            metadata: [
                'tool_calls' => $result['tool_calls'],
                'patient_id' => $data['patient_id'] ?? null,
            ],
        );

        return $parsed;
    }

    public function converse(
        string $systemPrompt,
        string $userMessage,
        array $previousMessages,
        array $tools,
        ?string $summary = null,
        ?int $userId = null,
    ): array {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        if ($summary) {
            $messages[] = [
                'role' => 'system',
                'content' => "Conversation summary:\n{$summary}",
            ];
        }

        foreach ($previousMessages as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $result = $this->provider->chatWithMessages($messages, $tools);

        AiPrompted::dispatch(
            userId: $userId,
            agent: 'PatientHealthAssistant',
            prompt: $userMessage,
            response: $result['content'],
            metadata: [
                'tool_calls' => $result['tool_calls'],
                'tool_results' => $result['tool_results'],
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'total_tokens' => $result['total_tokens'],
            ],
        );

        return $result;
    }

    private function parseStructuredResponse(string $content): array
    {
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $json = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return [
            'analysis' => $content,
            'urgency' => 'low',
            'recommended_specialties' => [],
            'recommended_doctors' => [],
            'follow_up_questions' => [],
        ];
    }
}
