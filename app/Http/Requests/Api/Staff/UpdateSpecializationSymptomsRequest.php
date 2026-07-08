<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpecializationSymptomsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'symptom_ids' => ['required', 'array', 'min:1'],
            'symptom_ids.*' => [
                'integer',
                Rule::exists('symptoms', 'id')->where('is_active', true),
            ],
        ];
    }
}
