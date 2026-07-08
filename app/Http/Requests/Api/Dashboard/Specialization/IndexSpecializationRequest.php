<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Dashboard\Specialization;

use Illuminate\Foundation\Http\FormRequest;

class IndexSpecializationRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'string', 'in:name,created_at,updated_at'],
            'order_by' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
