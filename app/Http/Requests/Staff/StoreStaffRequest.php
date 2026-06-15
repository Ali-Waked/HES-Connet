<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        info($this->all());
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'name' => ['required', 'array'],
            'name.en' => ['required_with:name', 'string'],
            'name.ar' => ['required_with:name', 'string'],
            // 'password' => ['required', 'string', 'min:8'],
            // 'role_id' => ['required', 'exists:roles,uuid'],
            'specialization' => ['nullable', 'array'],
            'specialization.en' => ['required_with:specialization', 'string', 'max:255'],
            'specialization.ar' => ['required_with:specialization', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'avatar' => ['required','image'],
            'cover_image' => ['nullable','image'],
            'bio' => ['nullable', 'array'],
            'bio.en' => ['required_with:bio', 'string'],
            'bio.ar' => ['required_with:bio', 'string'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'facilities' => ['required','array'],
            'facilities.*.facility_uuid' => ['required','exists:facilities,uuid'],
            'facilities.*.position_uuid' => ['required','exists:positions,uuid'],
            'facilities.*.department_uuid' => ['required','exists:departments,uuid'],
            'staff_position_uuid' => ['nullable', 'exists:staff_positions,uuid'],
        ];
    }
}
