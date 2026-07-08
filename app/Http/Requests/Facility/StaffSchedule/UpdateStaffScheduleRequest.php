<?php

namespace App\Http\Requests\Facility\StaffSchedule;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffScheduleRequest extends FormRequest
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
        info($this->all());

        return [
            'staff_uuid' => ['sometimes', 'required', 'exists:staff,uuid'],

            'days_of_week' => ['sometimes', 'required', 'array', 'min:1'],
            'days_of_week.*' => ['sometimes', 'required', 'integer', 'between:0,6', 'distinct'],

            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i', 'after:start_time'],

            'slot_duration' => ['sometimes', 'required', 'integer', 'min:5', 'max:240'],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
