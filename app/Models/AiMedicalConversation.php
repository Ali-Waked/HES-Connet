<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\AiMedicalConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid',
    'user_id',
    'title',
    'language',
    'status',
    'message_count',
    'total_tokens',
    'last_activity_at',
    'summary',
    'extracted_symptoms',
    'estimated_specialty',
    'urgency',
    'confidence',
    'triage_status',
    'recommended_at',
])]
class AiMedicalConversation extends Model
{
    /** @use HasFactory<AiMedicalConversationFactory> */
    use Auditable, HasFactory;

    use HasUuids;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'recommended_at' => 'datetime',
            'message_count' => 'integer',
            'total_tokens' => 'integer',
            'extracted_symptoms' => 'array',
            'confidence' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMedicalMessage::class, 'conversation_id');
    }

    public function lastMessage(): HasMany
    {
        return $this->hasMany(AiMedicalMessage::class, 'conversation_id')
            ->latest()
            ->limit(1);
    }

    public function scopeActive($query): void
    {
        $query->where('status', 'active');
    }

    public function isReadyForRecommendation(): bool
    {
        return $this->triage_status === 'ready';
    }

    public function markAsReady(): void
    {
        $this->update([
            'triage_status' => 'ready',
        ]);
    }

    public function markAsRecommended(): void
    {
        $this->update([
            'triage_status' => 'recommended',
            'recommended_at' => now(),
        ]);
    }

    public function updateTriageData(array $data): void
    {
        $this->update(array_filter([
            'extracted_symptoms' => $data['symptoms'] ?? null,
            'estimated_specialty' => $data['specialty'] ?? null,
            'urgency' => $data['urgency'] ?? null,
            'confidence' => $data['confidence'] ?? null,
            'triage_status' => ($data['ready_for_recommendation'] ?? false) ? 'ready' : $this->triage_status,
        ], fn ($v) => $v !== null));
    }
}
