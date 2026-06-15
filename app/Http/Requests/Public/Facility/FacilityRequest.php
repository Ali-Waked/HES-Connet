<?php

declare(strict_types=1);

namespace App\Http\Requests\Public\Facility;

use Illuminate\Foundation\Http\FormRequest;

class FacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'facility_type' => ['nullable', 'string', 'in:hospital,clinic,pharmacy,medical_point'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
