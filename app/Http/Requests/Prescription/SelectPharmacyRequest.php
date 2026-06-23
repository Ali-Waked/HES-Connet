<?php

declare(strict_types=1);

namespace App\Http\Requests\Prescription;

use Illuminate\Foundation\Http\FormRequest;

class SelectPharmacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'facility_id' => ['required', 'uuid', 'exists:facilities,uuid'],
        ];
    }
}
