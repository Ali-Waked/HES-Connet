<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AuditLogService
{
    private array $defaultIgnored = [
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

    public function log(
        string $action,
        ?string $tableName = null,
        ?string $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
        ?string $userName = null,
        ?string $userType = null,
    ): AuditLog {
        $request = request();
        $authenticatedUser = $request?->user();

        $data = [
            'user_id' => $userId ?? $authenticatedUser?->id,
            'user_name' => $userName ?? $authenticatedUser?->name ?? 'System',
            'user_type' => $userType ?? $this->resolveUserType($authenticatedUser),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId !== null ? (string) $recordId : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'request_method' => $request?->method(),
            'request_url' => $request?->fullUrl(),
        ];

        return AuditLog::create(Arr::whereNotNull($data));
    }

    public function logCreate(Model $model, ?int $userId = null): AuditLog
    {
        return $this->log(
            action: 'created',
            tableName: $model->getTable(),
            recordId: (string) $model->getKey(),
            newValues: $this->filterValues($model),
            userId: $userId,
        );
    }

    public function logUpdate(Model $model, ?array $oldValues = null, ?int $userId = null): AuditLog
    {
        $old = $oldValues ?? $this->filterValues($model->getOriginal());

        return $this->log(
            action: 'updated',
            tableName: $model->getTable(),
            recordId: (string) $model->getKey(),
            oldValues: $old,
            newValues: $this->filterValues($model),
            userId: $userId,
        );
    }

    public function logDelete(Model $model, ?int $userId = null): AuditLog
    {
        return $this->log(
            action: 'deleted',
            tableName: $model->getTable(),
            recordId: (string) $model->getKey(),
            oldValues: $this->filterValues($model),
            userId: $userId,
        );
    }

    public function logRestore(Model $model, ?int $userId = null): AuditLog
    {
        return $this->log(
            action: 'restored',
            tableName: $model->getTable(),
            recordId: (string) $model->getKey(),
            newValues: $this->filterValues($model),
            userId: $userId,
        );
    }

    public function logForceDelete(Model $model, ?int $userId = null): AuditLog
    {
        return $this->log(
            action: 'force_deleted',
            tableName: $model->getTable(),
            recordId: (string) $model->getKey(),
            oldValues: $this->filterValues($model),
            userId: $userId,
        );
    }

    public function logAuth(string $action, User $user): AuditLog
    {
        return $this->log(
            action: $action,
            tableName: $user->getTable(),
            recordId: (string) $user->getKey(),
            userId: $user->id,
            userName: $user->name ?? $user->email,
            userType: $this->resolveUserType($user),
        );
    }

    public function logBusiness(
        string $action,
        Model $model,
        ?int $userId = null,
        ?string $userName = null,
        ?string $userType = null,
    ): AuditLog {
        return $this->log(
            action: $action,
            tableName: $model->getTable(),
            recordId: (string) $model->getKey(),
            userId: $userId,
            userName: $userName,
            userType: $userType,
        );
    }

    private function filterValues(Model|array $model): array
    {
        $attributes = $model instanceof Model ? $model->toArray() : $model;

        $ignored = $this->defaultIgnored;

        if ($model instanceof Model && in_array(Auditable::class, class_uses_recursive($model), true)) {
            $ignored = array_merge($ignored, $model->getIgnoredAuditFields());
        }

        return Arr::except($attributes, $ignored);
    }

    private function resolveUserType(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        if ($user->hasSystemRole('super_admin')) {
            return 'super_admin';
        }

        if ($user->relationLoaded('staff') && $user->staff !== null) {
            return 'staff';
        }

        if ($user->relationLoaded('patient') && $user->patient !== null) {
            return 'patient';
        }

        return 'user';
    }
}
