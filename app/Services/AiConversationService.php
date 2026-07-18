<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\PatientHealthAssistant;
use App\Models\AiMedicalConversation;
use App\Models\AiMedicalMessage;
use App\Services\MedicalTriage\ConversationContextService;
use App\Services\MedicalTriage\ConversationSummaryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AiConversationService
{
    public function __construct(
        private readonly AiService $aiService,
        private readonly ConversationContextService $contextService,
        private readonly ConversationSummaryService $summaryService,
    ) {}

    public function listConversations(int $userId): LengthAwarePaginator
    {
        return AiMedicalConversation::where('user_id', $userId)
            ->with('lastMessage')
            ->orderBy('last_activity_at', 'desc')
            ->paginate(20);
    }

    public function createConversation(int $userId, ?string $title = null, ?string $message = null): AiMedicalConversation|array
    {
        $conversation = AiMedicalConversation::create([
            'user_id' => $userId,
            'title' => $title ?? config('chat.default_title', 'New Consultation'),
            'status' => 'active',
            'triage_status' => 'collecting',
            'message_count' => 0,
            'total_tokens' => 0,
            'last_activity_at' => now(),
        ]);

        $conversation = $conversation->fresh();

        if ($message) {
            return $this->respond(
                conversation: $conversation,
                userMessage: $message,
            );
        }

        return $conversation;
    }

    public function getConversation(string $uuid, int $userId): AiMedicalConversation
    {
        return AiMedicalConversation::where('uuid', $uuid)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function getMessages(AiMedicalConversation $conversation): LengthAwarePaginator
    {
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->paginate(50);
    }

    public function respond(
        AiMedicalConversation $conversation,
        string $userMessage,
    ): array {
        $maxMessages = config('chat.max_messages', 40);
        $maxTotalTokens = config('chat.max_total_tokens', 32000);

        if ($conversation->message_count >= $maxMessages) {
            return [
                'requires_new_conversation' => true,
                'reason' => 'Conversation reached maximum message count.',
            ];
        }

        if ($conversation->total_tokens >= $maxTotalTokens) {
            return [
                'requires_new_conversation' => true,
                'reason' => 'Conversation reached maximum context size.',
            ];
        }

        $context = $this->contextService->buildMessages($conversation);

        $userMessageRecord = $this->addMessage($conversation, 'user', $userMessage);

        $agent = new PatientHealthAssistant;

        $summaryText = $conversation->summary ?: null;

        $result = $this->aiService->converse(
            systemPrompt: $agent->instructions(),
            userMessage: $userMessage,
            previousMessages: $context,
            tools: [],
            summary: $summaryText,
        );

        $parsed = $this->parseResponse($result['content']);

        $this->updateConversationTriage($conversation, $parsed);

        $assistantMessageRecord = $this->addMessage(
            $conversation,
            'assistant',
            $parsed['analysis'] ?? $result['content'],
            metadata: [
                'urgency' => $parsed['urgency'] ?? null,
                'symptoms' => $parsed['symptoms'] ?? [],
                'follow_up_questions' => $parsed['follow_up_questions'] ?? [],
                'ready_for_recommendation' => $parsed['ready_for_recommendation'] ?? false,
                'language' => $parsed['language'] ?? null,
            ],
            promptTokens: $result['prompt_tokens'] ?? 0,
            completionTokens: $result['completion_tokens'] ?? 0,
        );

        $this->maybeCompressContext($conversation);

        if (! $conversation->language && ($parsed['language'] ?? null)) {
            $conversation->language = $parsed['language'];
            $conversation->save();
        }

        return [
            'conversation' => $conversation->fresh(),
            'user_message' => $userMessageRecord,
            'assistant_message' => $assistantMessageRecord,
        ];
    }

    public function addMessage(
        AiMedicalConversation $conversation,
        string $role,
        string $content,
        array $metadata = [],
        int $promptTokens = 0,
        int $completionTokens = 0,
    ): AiMedicalMessage {
        $totalTokens = $promptTokens + $completionTokens;

        $message = AiMedicalMessage::create([
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata ?: null,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
        ]);

        $conversation->increment('message_count');
        $conversation->increment('total_tokens', $totalTokens);
        $conversation->last_activity_at = now();
        $conversation->save();

        return $message;
    }

    private function updateConversationTriage(AiMedicalConversation $conversation, array $parsed): void
    {
        $existingSymptoms = $conversation->extracted_symptoms ?? [];
        $newSymptoms = $parsed['symptoms'] ?? [];
        $mergedSymptoms = array_unique(array_merge($existingSymptoms, $newSymptoms));

        $conversation->updateTriageData([
            'symptoms' => $mergedSymptoms,
            'urgency' => $parsed['urgency'] ?? $conversation->urgency,
            'ready_for_recommendation' => $parsed['ready_for_recommendation'] ?? false,
        ]);
    }

    private function parseResponse(string $content): array
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
            'symptoms' => [],
            'follow_up_questions' => [],
            'ready_for_recommendation' => false,
            'language' => null,
        ];
    }

    private function maybeCompressContext(AiMedicalConversation $conversation): void
    {
        $maxContextTokens = config('chat.max_context_tokens', 4000);

        if ($conversation->total_tokens < $maxContextTokens) {
            return;
        }

        if ($conversation->summary) {
            return;
        }

        $summaryData = $this->summaryService->extractSummary($conversation);
        $summaryText = $this->summaryService->buildSummaryText($summaryData);

        if ($summaryText) {
            $conversation->summary = $summaryText;
            $conversation->save();
        }
    }

    public function closeConversation(AiMedicalConversation $conversation): void
    {
        $conversation->update(['status' => 'closed']);
    }

    public function archiveConversation(AiMedicalConversation $conversation): void
    {
        $conversation->update(['status' => 'archived']);
    }

    public function deleteConversation(AiMedicalConversation $conversation): void
    {
        $conversation->messages()->delete();
        $conversation->delete();
    }
}
