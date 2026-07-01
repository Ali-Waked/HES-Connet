<?php

declare(strict_types=1);

namespace App\Notifications\Payments;

use App\Models\Invoice;
use App\Notifications\BaseNotification;

class InvoiceGeneratedNotification extends BaseNotification
{
    public static function forOwner(Invoice $invoice, ?string $locale = null): static
    {
        return new static(
            event: 'invoice.generated',
            role: 'owner',
            data: [
                'invoice_number' => $invoice->invoice_number ?? 'N/A',
                'total_amount' => number_format((float) $invoice->total_amount, 2),
                'currency' => $invoice->currency ?? 'USD',
                'action_text' => 'View Invoice',
                'action_url' => route('dashboard.invoices.show', $invoice),
            ],
            locale: $locale,
        );
    }
}
