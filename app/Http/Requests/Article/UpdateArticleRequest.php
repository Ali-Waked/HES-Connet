<?php

namespace App\Http\Requests\Article;

use Illuminate\Contracts\Validation\ValidationRule;
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
        info($this->all());
        return [
            'title' => ['sometimes', 'required', 'array'],
            'title.en' => ['sometimes', 'required', 'string', 'max:255'],
            'title.ar' => ['sometimes', 'required', 'string', 'max:255'],

            'content' => ['sometimes', 'required', 'array'],
            'content.en' => ['sometimes', 'required', 'string'],
            'content.ar' => ['sometimes', 'required', 'string'],

            'category_id' => ['sometimes', 'required', 'uuid'],
            'author_id' => ['sometimes', 'required', 'uuid'],

            'status' => ['sometimes', 'required', Rule::in(['draft', 'pending_review', 'published', 'archived', 'rejected'])],

            'tags' => ['sometimes', 'required', 'array', 'min:1'],
            'tags.*' => ['uuid'],

            'cover_image' => ['nullable', 'image', 'max:5120'],

            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:5120'],
        ];
    }
}
