<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\SearchHistory;

use Illuminate\Foundation\Http\FormRequest;

class FilterSearchHistoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'type' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
