<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $invoices = $this->invoiceService->getAllInvoices(
            $request->only(['date_from', 'date_to', 'payable_type', 'per_page'])
        );

        return InvoiceResource::collection($invoices)->response();
    }
}
