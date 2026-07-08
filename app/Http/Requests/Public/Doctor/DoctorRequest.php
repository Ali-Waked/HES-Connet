<?php

declare(strict_types=1);

namespace App\Http\Requests\Public\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'facility_id' => ['nullable', 'uuid', 'exists:facilities,uuid'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
