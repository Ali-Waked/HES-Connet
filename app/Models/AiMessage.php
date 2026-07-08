<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'conversation_id',
    'role',
    'content',
    'tool_name',
    'tool_arguments',
    'tool_result',
    'token_usage',
])]
class AiMessage extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'tool_arguments' => 'array',
            'tool_result' => 'array',
            'token_usage' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
