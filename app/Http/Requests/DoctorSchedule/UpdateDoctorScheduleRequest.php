<?php

declare(strict_types=1);

namespace App\Http\Requests\DoctorSchedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorScheduleRequest extends FormRequest
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
            'staff_id' => ['sometimes', 'required', 'exists:staff,id'],
            'day_of_week' => ['sometimes', 'required', 'integer', 'between:0,6'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['sometimes', 'required', 'integer', 'min:5'],
        ];
    }
}
