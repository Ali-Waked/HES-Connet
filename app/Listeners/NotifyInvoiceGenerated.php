<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceGenerated;
use App\Notifications\Payments\InvoiceGeneratedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyInvoiceGenerated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(InvoiceGenerated $event): void
    {
        $invoice = $event->invoice;
        $locale = app()->getLocale();

        $payable = $invoice->payable;
        $owner = $payable?->donor ?? $payable?->user ?? null;
        if ($owner) {
            $owner->notify(
                InvoiceGeneratedNotification::forOwner($invoice, $owner->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('invoice.generated', $owner?->id ?? 0, 'system');
    }
}
