<?php

declare(strict_types=1);

namespace App\Traits;

trait Auditable
{
    public function getIgnoredAuditFields(): array
    {
        return [
            'updated_at',
            'created_at',
            'remember_token',
            'password',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
            'email_verified_at',
            'last_seen_at',
            'pivot',
            'laravel_through_key',
        ];
    }
}
