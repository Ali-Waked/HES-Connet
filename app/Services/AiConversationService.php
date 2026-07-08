<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\PatientHealthAssistant;
use App\Models\AiMedicalConversation;
use App\Models\AiMedicalMessage;
use App\Models\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AiConversationService
{
    public function __construct(
        private readonly AiService $aiService,
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
        $conversation = AiMedicalConversation::where('uuid', $uuid)
            ->where('user_id', $userId)
            ->firstOrFail();

        return $conversation;
    }

    public function getMessages(AiMedicalConversation $conversation): LengthAwarePaginator
    {
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->paginate(50);
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

        $context = $this->buildContext($conversation);

        $userMessageRecord = $this->addMessage($conversation, 'user', $userMessage);

        $agent = new PatientHealthAssistant;

        $tools = [];
        foreach ($agent->tools() as $tool) {
            $tools[$tool->name()] = $tool;
        }

        $result = $this->aiService->converse(
            systemPrompt: $agent->instructions(),
            userMessage: $userMessage,
            previousMessages: $context['messages'],
            tools: $tools,
            summary: $conversation->summary,
        );

        $parsed = $this->parseResponse($result['content']);
        $parsed = $this->resolveDoctors($parsed, $result['tool_results'] ?? []);

        $cleanContent = $this->rebuildContent($result['content'], $parsed);

        $assistantMessageRecord = $this->addMessage(
            $conversation,
            'assistant',
            $cleanContent,
            metadata: [
                'tool_calls' => $result['tool_calls'],
                'tool_results' => $result['tool_results'],
                'analysis' => $parsed,
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

    private function buildContext(AiMedicalConversation $conversation): array
    {
        $recentCount = config('chat.context_recent_messages', 15);

        $messages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->take($recentCount)
            ->get()
            ->reverse()
            ->map(fn (AiMedicalMessage $msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->values()
            ->toArray();

        return ['messages' => $messages];
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

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->take(20)
            ->get();

        $text = $messages->map(fn ($m) => "{$m->role}: {$m->content}")->implode("\n\n");

        $summary = Str::limit($text, 1000);

        $conversation->summary = $summary;
        $conversation->save();
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
            'recommended_specialties' => [],
            'recommended_doctors' => [],
            'follow_up_questions' => [],
        ];
    }

    private function resolveDoctors(array $parsed, array $toolResults): array
    {
        $realDoctors = [];

        foreach ($toolResults as $tr) {
            if (($tr['tool'] ?? '') === 'get_doctors_by_specialty' && ! empty($tr['result'])) {
                foreach ($tr['result'] as $doctor) {
                    if (isset($doctor['uuid'])) {
                        $realDoctors[$doctor['uuid']] = [
                            'uuid' => $doctor['uuid'],
                            'name' => $doctor['name'] ?? '',
                            'specialty' => is_array($doctor['specialization'] ?? null)
                                ? ($doctor['specialization']['en'] ?? '')
                                : ($doctor['specialization'] ?? ''),
                        ];
                    }
                }
            }
        }

        if (empty($realDoctors) && ! empty($parsed['recommended_specialties'])) {
            $realDoctors = $this->findDoctorsBySpecialties((array) $parsed['recommended_specialties']);
        }

        if (empty($realDoctors) && ! empty($parsed['analysis'])) {
            $realDoctors = $this->findDoctorsBySymptoms($parsed['analysis']);
        }

        $parsed['recommended_doctors'] = array_values($realDoctors);

        return $parsed;
    }

    private function findDoctorsBySpecialties(array $specialties): array
    {
        if (empty($specialties)) {
            return [];
        }

        $doctors = Staff::query()
            ->with([
                'user:id,name,email',
                'specialization',
            ])
            ->whereHas('facilityStaff', fn (Builder $q) => $q
                ->whereNull('ended_at')
                ->whereHas('role', fn (Builder $r) => $r->where('slug', 'doctor_portal_user'))
            )
            ->whereHas('specialization', function (Builder $q) use ($specialties) {
                $q->where(function (Builder $inner) use ($specialties) {
                    foreach ($specialties as $specialty) {
                        $term = mb_strtolower(trim($specialty));
                        $inner->orWhere('name->en', 'like', "%{$term}%")
                            ->orWhere('name->ar', 'like', "%{$term}%");
                    }
                });
            })
            ->limit(10)
            ->get();

        return $this->mapDoctors($doctors);
    }

    private function findDoctorsBySymptoms(string $analysis): array
    {
        if (empty(trim($analysis))) {
            return [];
        }

        $doctors = Staff::query()
            ->with([
                'user:id,name,email',
                'specialization',
            ])
            ->whereHas('facilityStaff', fn (Builder $q) => $q
                ->whereNull('ended_at')
                ->whereHas('role', fn (Builder $r) => $r->where('slug', 'doctor_portal_user'))
            )
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return $this->mapDoctors($doctors);
    }

    private function mapDoctors(iterable $doctors): array
    {
        $mapped = [];

        foreach ($doctors as $doctor) {
            $mapped[$doctor->uuid] = [
                'uuid' => $doctor->uuid,
                'name' => $doctor->user?->getTranslations('name'),
                'specialty' => $doctor->specialization?->getTranslations('name'),
            ];
        }

        return $mapped;
    }

    private function rebuildContent(string $originalContent, array $parsed): string
    {
        $jsonStart = strpos($originalContent, '{');
        $jsonEnd = strrpos($originalContent, '}');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $prefix = substr($originalContent, 0, $jsonStart);
            $suffix = substr($originalContent, $jsonEnd + 1);

            return $prefix.json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).$suffix;
        }

        return $originalContent;
    }

    public function closeConversation(AiMedicalConversation $conversation): void
    {
        $conversation->status = 'closed';
        $conversation->save();
    }

    public function archiveConversation(AiMedicalConversation $conversation): void
    {
        $conversation->status = 'archived';
        $conversation->save();
    }

    public function deleteConversation(AiMedicalConversation $conversation): void
    {
        $conversation->messages()->delete();
        $conversation->delete();
    }
}
