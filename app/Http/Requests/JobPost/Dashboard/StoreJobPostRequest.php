<?php

namespace App\Http\Requests\JobPost\Dashboard;

use App\Enums\ApplyMethod;
use App\Enums\CategoriesType;
use App\Enums\EmploymentType;
use App\Enums\ExperienceLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreJobPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                Rule::exists('categories', 'uuid')->where('type', CategoriesType::JOB->value),
            ],
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.ar' => ['required', 'string', 'max:255'],

            'content' => ['required', 'array'],
            'content.en' => ['required', 'string'],
            'content.ar' => ['required', 'string'],

            'employment_type' => ['required', new Enum(EmploymentType::class)],
            'experience_level' => ['required', new Enum(ExperienceLevel::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'salary_from' => ['nullable', 'numeric', 'min:0'],
            'salary_to' => ['nullable', 'numeric', 'min:0', 'gte:salary_from'],
            'salary_currency' => ['nullable', 'string', 'size:3'],
            'is_salary_visible' => ['boolean'],
            'vacancies' => ['nullable', 'integer', 'min:1'],
            'apply_method' => ['required', new Enum(ApplyMethod::class)],
            'apply_value' => ['required', 'string', 'max:255'],
            'end_date' => ['required', 'date', 'after:today'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $applyMethod = $this->input('apply_method');
            $applyValue = $this->input('apply_value');

            if ($applyMethod === ApplyMethod::EMAIL->value && ! filter_var($applyValue, FILTER_VALIDATE_EMAIL)) {
                $validator->errors()->add('apply_value', __('validation.email', ['attribute' => 'apply_value']));
            }

            if ($applyMethod === ApplyMethod::LINK->value && ! filter_var($applyValue, FILTER_VALIDATE_URL)) {
                $validator->errors()->add('apply_value', __('validation.url', ['attribute' => 'apply_value']));
            }
        });
    }
}
