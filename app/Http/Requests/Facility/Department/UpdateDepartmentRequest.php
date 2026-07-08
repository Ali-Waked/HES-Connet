<?php

declare(strict_types=1);

namespace App\Http\Requests\Facility\Department;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'array'],
            'name.en' => ['sometimes', 'required', 'string', 'max:255'],
            'name.ar' => ['sometimes', 'required', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],

            'image' => ['nullable', 'image', 'max:5120'],

            'head_facility_staff_uuid' => ['nullable', 'uuid', 'exists:facility_staff,uuid'],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
