<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class AuditModelObserver
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function created(Model $model): void
    {
        if (! $this->shouldAudit($model)) {
            return;
        }

        $this->auditLogService->logCreate($model);
    }

    public function updated(Model $model): void
    {
        if (! $this->shouldAudit($model)) {
            return;
        }

        if ($model->isDirty()) {
            $this->auditLogService->logUpdate($model);
        }
    }

    public function deleted(Model $model): void
    {
        if (! $this->shouldAudit($model)) {
            return;
        }

        $this->auditLogService->logDelete($model);
    }

    public function restored(Model $model): void
    {
        if (! $this->shouldAudit($model)) {
            return;
        }

        $this->auditLogService->logRestore($model);
    }

    public function forceDeleted(Model $model): void
    {
        if (! $this->shouldAudit($model)) {
            return;
        }

        $this->auditLogService->logForceDelete($model);
    }

    private function shouldAudit(Model $model): bool
    {
        if ($model instanceof AuditLog) {
            return false;
        }

        return in_array(Auditable::class, class_uses_recursive($model), true);
    }
}
