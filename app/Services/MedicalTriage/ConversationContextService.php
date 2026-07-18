<?php

declare(strict_types=1);

namespace App\Services\MedicalTriage;

use App\Models\AiMedicalConversation;
use App\Models\AiMedicalMessage;

class ConversationContextService
{
    public function buildMessages(AiMedicalConversation $conversation, int $recentCount = 15): array
    {
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

        return $messages;
    }

    public function buildFullConversationText(AiMedicalConversation $conversation): string
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

    public function getConversationLanguage(AiMedicalConversation $conversation): ?string
    {
        if ($conversation->language) {
            return $conversation->language;
        }

        $lastUserMessage = $conversation->messages()
            ->where('role', 'user')
            ->latest()
            ->first();

        if (! $lastUserMessage) {
            return null;
        }

        return $this->detectLanguage($lastUserMessage->content);
    }

    private function detectLanguage(string $text): string
    {
        $arabicChars = preg_match_all('/[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/u', $text);
        $totalChars = mb_strlen(preg_replace('/\s+/u', '', $text));

        if ($totalChars === 0) {
            return 'en';
        }

        return ($arabicChars / $totalChars) > 0.3 ? 'ar' : 'en';
    }
}
