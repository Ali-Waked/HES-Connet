<?php

declare(strict_types=1);

namespace App\Http\Requests\StaffUnavailability;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffUnavailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_uuid' => ['required', 'exists:staff,uuid'],
            'start_at' => ['required', 'date', 'after_or_equal:now'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
