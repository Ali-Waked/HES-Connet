<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnavailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'facility_id' => ['sometimes', 'required', 'exists:facilities,uuid'],
            'start_at' => ['sometimes', 'required', 'date', 'after_or_equal:now'],
            'end_at' => ['sometimes', 'required', 'date', 'after:start_at'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
