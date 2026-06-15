<?php

namespace App\Http\Requests\Department;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
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
            'name' => ['sometimes','required','array'],
            'name.en' => ['sometimes','required', 'string', 'max:255'],
            'name.ar' => ['sometimes','required', 'string', 'max:255'],
            
            'description' => ['nullable','array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],

            'facility_id' => [
                'sometimes','required',
                'exists:facilities,uuid',
            ],

            'head_id' => [
                'nullable',
                'exists:staff,uuid',
            ],

            'is_active' => ['nullable','boolean'],
            'image' => ['sometimes','required','image']
        ];

    }
}
