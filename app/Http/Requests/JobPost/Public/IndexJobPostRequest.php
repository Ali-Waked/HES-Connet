<?php

namespace App\Http\Requests\JobPost\Public;

use Illuminate\Foundation\Http\FormRequest;

class IndexJobPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
            'facility_id' => ['nullable', 'string', 'exists:facilities,uuid'],
            'category_id' => ['nullable', 'string', 'exists:categories,uuid'],
            'employment_type' => ['nullable', 'string'],
            'experience_level' => ['nullable', 'string'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string'],
            'salary_from' => ['nullable', 'numeric', 'min:0'],
            'salary_to' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date'],
            'end_before' => ['nullable', 'date'],
            'end_after' => ['nullable', 'date'],
            'sort' => ['nullable', 'string', 'in:latest,oldest,most_viewed,ending_soon,featured'],
        ];
    }
}
