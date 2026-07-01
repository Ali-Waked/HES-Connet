<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\SearchHistory;

use Illuminate\Foundation\Http\FormRequest;

class StoreSearchHistoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'filters' => ['nullable', 'array'],
        ];
    }
}
