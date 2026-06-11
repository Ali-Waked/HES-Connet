<?php

declare(strict_types=1);

namespace App\Http\Requests\DoctorUnavailable;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorUnavailableRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
