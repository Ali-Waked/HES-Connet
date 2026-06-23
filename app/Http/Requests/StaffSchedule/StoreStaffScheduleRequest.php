<?php

declare(strict_types=1);

namespace App\Http\Requests\StaffSchedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'facility_staff_uuid' => ['required', 'exists:facility_staff,uuid'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['required', 'integer', 'min:5'],
            'is_active' => ['boolean'],
        ];
    }
}
