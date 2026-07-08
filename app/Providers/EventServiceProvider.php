<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\AiPrompted;
use App\Listeners\Audit\LogBusinessEvent;
use App\Listeners\Audit\LogUserAuth;
use App\Listeners\DispatchNotification;
use App\Listeners\LogAiPrompt;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $subscribe = [
        LogUserAuth::class,
        LogBusinessEvent::class,
        DispatchNotification::class,
    ];

    protected $listen = [
        AiPrompted::class => [
            LogAiPrompt::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
