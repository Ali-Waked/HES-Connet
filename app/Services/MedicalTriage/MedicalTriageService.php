<?php

declare(strict_types=1);

namespace App\Services\MedicalTriage;

use App\Ai\Agents\MedicalTriageAgent;
use App\DTOs\TriageResultDto;
use App\Models\AiMedicalConversation;
use App\Services\AiService;

class MedicalTriageService
{
    public function __construct(
        private readonly AiService $aiService,
    ) {}

    public function analyze(AiMedicalConversation $conversation): TriageResultDto
    {
        $fullConversation = $this->buildFullConversationText($conversation);

        $agent = new MedicalTriageAgent;

        $result = $this->aiService->askRaw(
            systemPrompt: $agent->instructions(),
            userMessage: $fullConversation,
        );

        $parsed = $this->parseJsonResponse($result);

        return TriageResultDto::fromArray($parsed);
    }

    private function buildFullConversationText(AiMedicalConversation $conversation): string
    {
        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        $lines = [];
        foreach ($messages as $msg) {
            $role = $msg->role === 'user' ? 'Patient' : 'Assistant';
            $lines[] = "{$role}: {$msg->content}";
        }

        return implode("\n\n", $lines);
    }

    private function parseJsonResponse(string $content): array
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
            'specialty' => 'General Practice',
            'urgency' => 'low',
            'confidence' => 0.3,
            'symptoms' => [],
        ];
    }
}
