<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'array'],
            'title.en' => ['sometimes', 'required', 'string', 'max:255'],
            'title.ar' => ['sometimes', 'required', 'string', 'max:255'],

            'content' => ['sometimes', 'required', 'array'],
            'content.en' => ['sometimes', 'required', 'string'],
            'content.ar' => ['sometimes', 'required', 'string'],

            'category_id' => ['sometimes', 'required', 'uuid', 'exists:categories,uuid'],

            'status' => ['sometimes', 'required', Rule::in(['draft', 'pending_review', 'published', 'archived'])],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['uuid', 'exists:tags,uuid'],

            'cover_image' => ['nullable', 'image', 'max:5120', 'mimes:jpeg,png,jpg,webp'],
        ];
    }
}
