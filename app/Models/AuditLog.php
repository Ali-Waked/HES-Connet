<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'user_name',
    'user_type',
    'action',
    'table_name',
    'record_id',
    'old_values',
    'new_values',
    'ip_address',
    'user_agent',
    'request_method',
    'request_url',
])]
class AuditLog extends Model
{
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
