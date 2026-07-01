<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class InvoiceService
{
    public function generateForPayable(Model $payable): Invoice
    {
        $invoiceNumber = 'INV-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

        return Invoice::create([
            'payable_type' => $payable->getMorphClass(),
            'payable_id' => $payable->id,
            'invoice_number' => $invoiceNumber,
            'total_amount' => $payable->amount,
            'currency' => $payable->currency ?? 'SAR',
            'issued_at' => now(),
            'metadata' => [
                'type' => 'payment',
                'payment_id' => $payable->id,
                'generated_by' => 'system',
            ],
        ]);
    }

    public function getAllInvoices(array $filters = []): LengthAwarePaginator
    {
        return Invoice::query()
            ->with(['payable'])
            ->when(
                $filters['date_from'] ?? null,
                fn ($q, $v) => $q->whereDate('created_at', '>=', $v)
            )
            ->when(
                $filters['date_to'] ?? null,
                fn ($q, $v) => $q->whereDate('created_at', '<=', $v)
            )
            ->when(
                $filters['payable_type'] ?? null,
                fn ($q, $v) => $q->where('payable_type', $v)
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
