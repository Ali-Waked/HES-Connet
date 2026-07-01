<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'invoice_number' => $this->invoice_number,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'pdf_url' => $this->pdf_url,
            'issued_at' => $this->issued_at,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'created_at' => $this->created_at,
        ];
    }
}
