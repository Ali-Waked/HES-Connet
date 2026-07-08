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
            'message_count' => 'integer',
            'total_tokens' => 'integer',
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
}
