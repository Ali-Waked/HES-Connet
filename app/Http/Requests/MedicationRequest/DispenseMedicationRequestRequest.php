<?php

declare(strict_types=1);

namespace App\Http\Requests\MedicationRequest;

use Illuminate\Foundation\Http\FormRequest;

class DispenseMedicationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
