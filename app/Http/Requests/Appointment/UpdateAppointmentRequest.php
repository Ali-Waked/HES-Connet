<?php

declare(strict_types=1);

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_at' => ['sometimes', 'required', 'date', 'after_or_equal:now'],
            'end_at' => ['sometimes', 'required', 'date', 'after:start_at'],
            'status' => ['sometimes', 'required', 'string', 'in:scheduled,confirmed,checked_in,in_progress,completed,cancelled,no_show,rescheduled'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
