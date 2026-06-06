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
            'facility_id' => [
                'sometimes',
                'required',
                'exists:facilities,uuid',
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'head_id' => [
                'sometimes',
                'required',
                'exists:staff,uuid',
            ],
        ];
    }
}
