<?php

declare(strict_types=1);

namespace App\Ai\Providers;

use App\Ai\Contracts\AiProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AnthropicProvider implements AiProvider
{
    private readonly string $apiKey;

    private readonly string $model;

    private readonly string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.anthropic.api_key')
            ?? throw new RuntimeException('ANTHROPIC_API_KEY is not set in .env');
        $this->model = config('ai.providers.anthropic.model');
        $this->baseUrl = config('ai.providers.anthropic.base_url', 'https://api.anthropic.com/v1');
    }

    public function chat(string $systemPrompt, string $userMessage, array $tools = []): string
    {
        $response = $this->client()->post("{$this->baseUrl}/messages", array_filter([
            'model' => $this->model,
            'max_tokens' => 4096,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
            'tools' => $this->formatTools($tools) ?: null,
        ]))->throw();

        $data = $response->json();

        return $this->extractContent($data);
    }

    public function chatWithTools(string $systemPrompt, string $userMessage, array $tools): array
    {
        $formattedTools = $this->formatTools($tools);

        $response = $this->client()->post("{$this->baseUrl}/messages", array_filter([
            'model' => $this->model,
            'max_tokens' => 4096,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
            'tools' => $formattedTools ?: null,
        ]))->throw();

        $data = $response->json();
        $content = $this->extractContent($data);
        $toolBlocks = $this->extractToolBlocks($data);

        $results = [];

        foreach ($toolBlocks as $block) {
            $toolName = $block['name'];
            $arguments = $block['input'] ?? [];

            if (isset($tools[$toolName])) {
                try {
                    $result = $tools[$toolName]($arguments);
                    $results[] = [
                        'tool' => $toolName,
                        'result' => $result,
                    ];
                } catch (\Throwable $e) {
                    Log::warning("AI tool {$toolName} failed", [
                        'error' => $e->getMessage(),
                    ]);
                    $results[] = [
                        'tool' => $toolName,
                        'result' => ['error' => $e->getMessage()],
                    ];
                }
            }
        }

        if (! empty($toolBlocks) && ! empty($results)) {
            $messages = [
                ['role' => 'user', 'content' => $userMessage],
                [
                    'role' => 'assistant',
                    'content' => array_merge(
                        $content ? [['type' => 'text', 'text' => $content]] : [],
                        array_map(fn ($b) => [
                            'type' => 'tool_use',
                            'id' => $b['id'],
                            'name' => $b['name'],
                            'input' => $b['input'],
                        ], $toolBlocks),
                    ),
                ],
            ];

            foreach ($results as $result) {
                $messages[] = [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $toolBlocks[array_search($result['tool'], array_column($toolBlocks, 'name'))]['id'] ?? '',
                            'content' => json_encode($result['result']),
                        ],
                    ],
                ];
            }

            $finalResponse = $this->client()->post("{$this->baseUrl}/messages", [
                'model' => $this->model,
                'max_tokens' => 4096,
                'system' => $systemPrompt,
                'messages' => $messages,
            ])->throw();

            $finalData = $finalResponse->json();
            $content = $this->extractContent($finalData);
        }

        return [
            'content' => $content,
            'tool_calls' => $toolBlocks,
            'tool_results' => $results,
        ];
    }

    public function chatWithMessages(array $messages, array $tools): array
    {
        $lastUserMessage = '';
        $systemPrompt = '';

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemPrompt = $msg['content'];
            }
            if ($msg['role'] === 'user') {
                $lastUserMessage = $msg['content'];
            }
        }

        return $this->chatWithTools($systemPrompt, $lastUserMessage, $tools);
    }

    private function client(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ])
            ->acceptJson()
            ->timeout(120);
    }

    private function formatTools(array $tools): array
    {
        if (empty($tools)) {
            return [];
        }

        return array_map(fn (array $tool) => [
            'name' => $tool['name'],
            'description' => $tool['description'] ?? '',
            'input_schema' => $tool['parameters'] ?? [
                'type' => 'object',
                'properties' => [],
            ],
        ], $tools);
    }

    private function extractContent(array $data): string
    {
        $parts = [];

        foreach ($data['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $parts[] = $block['text'];
            }
        }

        return implode("\n", $parts);
    }

    private function extractToolBlocks(array $data): array
    {
        $blocks = [];

        foreach ($data['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'tool_use') {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }
}
