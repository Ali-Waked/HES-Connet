<?php

declare(strict_types=1);

namespace App\Http\Requests\StaffSchedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_uuid' => ['sometimes', 'required', 'exists:staff,uuid'],
            'facility_uuid' => ['sometimes', 'required', 'exists:facilities,uuid'],
            'day_of_week' => ['sometimes', 'required', 'integer', 'between:0,6'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['sometimes', 'required', 'integer', 'min:5'],
        ];
    }
}
