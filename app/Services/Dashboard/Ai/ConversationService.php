<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Ai;

use App\Models\AiConversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ConversationService
{
    public function list(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return AiConversation::where('user_id', $userId)
            ->orderBy('last_message_at', 'desc')
            ->paginate($perPage);
    }

    public function getByUuid(string $uuid, int $userId): AiConversation
    {
        return AiConversation::where('uuid', $uuid)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function create(int $userId, string $language, ?string $title = null): AiConversation
    {
        return AiConversation::create([
            'user_id' => $userId,
            'title' => $title,
            'language' => $language,
            'last_message_at' => now(),
        ]);
    }

    public function updateLastMessageAt(AiConversation $conversation): void
    {
        $conversation->updateQuietly(['last_message_at' => now()]);
    }

    public function rename(AiConversation $conversation, string $title): AiConversation
    {
        $conversation->update(['title' => $title]);

        return $conversation->fresh();
    }

    public function delete(AiConversation $conversation): void
    {
        $conversation->delete();
    }

    public function generateTitle(string $message, string $language): string
    {
        $plainText = strip_tags($message);
        $truncated = mb_substr($plainText, 0, 60);

        if (mb_strlen($plainText) <= 60) {
            return $truncated;
        }

        return $truncated.'...';
    }

    public function detectLanguage(string $message): string
    {
        if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $message)) {
            return 'ar';
        }

        return 'en';
    }
}
