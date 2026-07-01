<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Dashboard\Symptom;

use App\Enums\SymptomOrderBy;
use App\Enums\SymptomSortBy;
use App\Enums\SymptomStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class IndexSymptomRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', new Enum(SymptomStatus::class)],
            'sort_by' => ['nullable', new Enum(SymptomSortBy::class)],
            'order_by' => ['nullable', new Enum(SymptomOrderBy::class)],
        ];
    }
}
