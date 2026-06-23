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
        info($this->all());
        return [
            'user_id' => ['sometimes', 'required', 'exists:users,id'],
            'name' => ['sometimes', 'required', 'array'],
            'name.en' => ['required_with:name', 'string'],
            'name.ar' => ['required_with:name', 'string'],
            'role_id' => ['sometimes', 'required', 'exists:roles,uuid'],
            'specialization' => ['nullable', 'array'],
            'specialization.en' => ['required_with:specialization', 'string', 'max:255'],
            'specialization.ar' => ['required_with:specialization', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'avatar' => ['nullable', 'image'],
            'cover_image' => ['nullable', 'image'],
            'bio' => ['nullable', 'array'],
            'bio.en' => ['required_with:bio', 'string'],
            'bio.ar' => ['required_with:bio', 'string'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'facilities' => ['sometimes', 'required', 'array'],
            'facilities.*.facility_uuid' => ['required', 'exists:facilities,uuid'],
            'facilities.*.position_uuid' => ['nullable', 'exists:positions,uuid'],
            'facilities.*.department_uuid' => ['nullable', 'exists:departments,uuid'],
            'facilities.*.role_uuid' => ['required', 'exists:roles,uuid,scope,facility,is_active,1'],
            'staff_position_uuid' => ['nullable', 'exists:staff_positions,uuid'],
        ];
    }
}
