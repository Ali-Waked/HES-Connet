<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date', 'before_or_equal:to_date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'facility' => ['nullable', 'string', 'exists:facilities,uuid'],
            'department' => ['nullable', 'string', 'exists:departments,uuid'],
            'status' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'exists:categories,uuid'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
