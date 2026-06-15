<?php

declare(strict_types=1);

namespace App\Http\Requests\Prescription;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'exists:appointments,id'],
            'doctor_id' => ['required', 'exists:staff,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
