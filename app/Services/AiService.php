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

        $toolArrays = [];
        foreach ($tools as $name => $tool) {
            $toolArrays[$name] = $tool->toArray();
        }

        $result = $this->provider->chatWithTools(
            $agent->instructions(),
            $message,
            $toolArrays,
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

        $toolArrays = [];
        foreach ($tools as $name => $tool) {
            $toolArrays[$name] = $tool->toArray();
        }

        $result = $this->provider->chatWithTools(
            $agent->instructions(),
            $data['message'],
            $toolArrays,
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
