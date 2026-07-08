<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CalendarIndexRequest extends FormRequest
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
            'facility_uuid' => ['nullable', 'string', 'exists:facilities,uuid'],
            'week_start' => ['nullable', 'date'],
            'week_end' => ['nullable', 'date'],
        ];
    }
}
