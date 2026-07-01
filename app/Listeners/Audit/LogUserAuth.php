<?php

declare(strict_types=1);

namespace App\Listeners\Audit;

use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

class LogUserAuth
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function handleLogin(UserLoggedIn $event): void
    {
        $this->auditLogService->logAuth('login', $event->user);
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            $this->auditLogService->logAuth('logout', $event->user);
        }
    }

    public function handleRegister(UserRegistered $event): void
    {
        $this->auditLogService->logAuth('registered', $event->user);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->auditLogService->logAuth('password_reset', $event->user);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(UserLoggedIn::class, [self::class, 'handleLogin']);
        $events->listen(Logout::class, [self::class, 'handleLogout']);
        $events->listen(UserRegistered::class, [self::class, 'handleRegister']);
        $events->listen(PasswordReset::class, [self::class, 'handlePasswordReset']);
    }
}
