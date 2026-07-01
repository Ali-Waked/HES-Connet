<?php

declare(strict_types=1);

namespace App\Ai\Providers;

use App\Ai\Contracts\AiProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProvider
{
    private readonly string $apiKey;

    private readonly string $model;

    private readonly string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.openai.api_key');
        $this->model = config('ai.providers.openai.model');
        $this->baseUrl = config('ai.providers.openai.base_url', 'https://api.openai.com/v1');
    }

    public function chat(string $systemPrompt, string $userMessage, array $tools = []): string
    {
        $response = $this->client()->post("{$this->baseUrl}/chat/completions", array_filter([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'tools' => $this->formatTools($tools) ?: null,
        ]))->throw();

        $data = $response->json();

        return $data['choices'][0]['message']['content'] ?? '';
    }

    public function chatWithTools(string $systemPrompt, string $userMessage, array $tools): array
    {
        $formattedTools = $this->formatTools($tools);

        $response = $this->client()->post("{$this->baseUrl}/chat/completions", array_filter([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'tools' => $formattedTools ?: null,
            'tool_choice' => 'auto',
        ]))->throw();

        $data = $response->json();
        $message = $data['choices'][0]['message'] ?? [];
        $content = $message['content'] ?? '';
        $toolCalls = $message['tool_calls'] ?? [];

        $results = [];

        foreach ($toolCalls as $call) {
            $functionName = $call['function']['name'];
            $arguments = json_decode($call['function']['arguments'], true) ?? [];

            if (isset($tools[$functionName])) {
                try {
                    $result = $tools[$functionName]($arguments);
                    $results[] = [
                        'tool' => $functionName,
                        'result' => $result,
                    ];
                } catch (\Throwable $e) {
                    Log::warning("AI tool {$functionName} failed", [
                        'error' => $e->getMessage(),
                    ]);
                    $results[] = [
                        'tool' => $functionName,
                        'result' => ['error' => $e->getMessage()],
                    ];
                }
            }
        }

        if (! empty($toolCalls) && ! empty($results)) {
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
                ['role' => 'assistant', 'content' => $content, 'tool_calls' => $toolCalls],
            ];

            foreach ($results as $result) {
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCalls[array_search($result['tool'], array_column($toolCalls, 'function.name'))]['id'] ?? '',
                    'content' => json_encode($result['result']),
                ];
            }

            $finalResponse = $this->client()->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
            ])->throw();

            $finalData = $finalResponse->json();
            $content = $finalData['choices'][0]['message']['content'] ?? '';
        }

        return [
            'content' => $content,
            'tool_calls' => $toolCalls,
            'tool_results' => $results,
        ];
    }

    private function client(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->acceptJson()
            ->timeout(120);
    }

    private function formatTools(array $tools): array
    {
        if (empty($tools)) {
            return [];
        }

        return array_map(fn (array $tool) => [
            'type' => 'function',
            'function' => [
                'name' => $tool['name'],
                'description' => $tool['description'] ?? '',
                'parameters' => $tool['parameters'] ?? [
                    'type' => 'object',
                    'properties' => [],
                ],
            ],
        ], $tools);
    }
}
