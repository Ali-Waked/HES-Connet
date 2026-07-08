<?php

namespace App\Http\Requests\Facility;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'name' => ['required', 'array'],
            'name.en' => ['required_with:name', 'string'],
            'name.ar' => ['required_with:name', 'string'],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'avatar' => ['nullable', 'image'],
            'cover_image' => ['nullable', 'image'],
            'bio' => ['nullable', 'array'],
            'bio.en' => ['required_with:bio', 'string'],
            'bio.ar' => ['required_with:bio', 'string'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'position_uuid' => ['nullable', 'exists:positions,uuid'],
            'department_uuid' => ['nullable', 'exists:departments,uuid'],
            'role_uuid' => ['required', 'exists:roles,uuid,scope,facility,is_active,1'],
            // 'staff_position_uuid' => ['nullable', 'exists:staff_positions,uuid'],
        ];
    }
}
