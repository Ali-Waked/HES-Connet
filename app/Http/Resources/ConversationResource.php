<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'language' => $this->language,
            'status' => $this->status,
            'message_count' => $this->message_count,
            'total_tokens' => $this->total_tokens,
            'last_activity_at' => $this->last_activity_at?->toIso8601String(),
            'summary' => $this->summary,
            'extracted_symptoms' => $this->extracted_symptoms ?? [],
            'estimated_specialty' => $this->estimated_specialty,
            'urgency' => $this->urgency,
            'confidence' => $this->confidence,
            'triage_status' => $this->triage_status,
            'recommended_at' => $this->recommended_at?->toIso8601String(),
            'last_message_preview' => $this->relationLoaded('lastMessage')
                ? Str::limit($this->lastMessage->first()?->content, 120)
                : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
