<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use App\Enums\CategoriesType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'array'],
            'name.en' => ['sometimes', 'required', 'string', 'max:255'],
            'name.ar' => ['sometimes', 'required', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],

            'type' => ['sometimes', 'required', new Enum(CategoriesType::class)],

            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
