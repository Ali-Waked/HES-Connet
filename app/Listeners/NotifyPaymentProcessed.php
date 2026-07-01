<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentProcessed;
use App\Notifications\Payments\PaymentProcessedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyPaymentProcessed
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(PaymentProcessed $event): void
    {
        $payment = $event->payment;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                PaymentProcessedNotification::forAdmin($payment, $admin->locale ?? $locale),
            );
        }

        $this->logService->markSent('payment.processed', $payment->donation?->donor_id ?? 0, 'system');
    }
}
