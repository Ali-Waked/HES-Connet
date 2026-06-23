<?php

declare(strict_types=1);

namespace App\Http\Requests\Prescription;

use App\Enums\PrescriptionRoute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        info($this->all());

        return [
            'appointment_id' => ['required', 'exists:appointments,uuid'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'medicines' => ['required', 'array', 'min:1'],
            'medicines.*.medicine_id' => ['required', 'exists:medicines,uuid'],
            'medicines.*.dosage' => ['required', 'string', 'max:255'],
            'medicines.*.frequency' => ['required', 'string', 'max:255'],
            'medicines.*.duration' => ['nullable', 'string', 'max:255'],
            'medicines.*.route' => ['nullable', 'string', Rule::enum(PrescriptionRoute::class)],
            'medicines.*.instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
