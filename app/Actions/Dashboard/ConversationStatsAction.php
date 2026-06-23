<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\ConversationType;
use App\Models\Conversation;
use App\Models\Message;

class ConversationStatsAction
{
    public function execute(): array
    {
        return [
            'total_conversations' => Conversation::count(),
            'support_conversations' => Conversation::where('type', ConversationType::SUPPORT)->count(),
            'doctor_patient_conversations' => Conversation::where('type', ConversationType::DOCTOR_PATIENT)->count(),
            'active_conversations' => Conversation::where('status', 'active')->count(),
            'messages_today' => Message::whereDate('created_at', today())->count(),
        ];
    }
}
