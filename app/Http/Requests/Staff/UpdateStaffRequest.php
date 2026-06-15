<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
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
            'user_id' => ['sometimes', 'required', 'exists:users,id'],
            'specialization' => ['nullable', 'array'],
            'specialization.en' => ['required_with:specialization', 'string', 'max:255'],
            'specialization.ar' => ['required_with:specialization', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'bio' => ['nullable', 'array'],
            'bio.en' => ['required_with:bio', 'string'],
            'bio.ar' => ['required_with:bio', 'string'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'staff_position_uuid' => ['nullable', 'exists:staff_positions,uuid'],
        ];
    }
}
