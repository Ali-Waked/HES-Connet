<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Dashboard\Specialization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncSpecializationSymptomsRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'symptom_ids' => ['required', 'array'],
            'symptom_ids.*' => [
                'integer',
                Rule::exists('symptoms', 'id')->where('is_active', true),
            ],
        ];
    }
}
