<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.ar' => ['required', 'string', 'max:255'],

            'content' => ['required', 'array'],
            'content.en' => ['required', 'string'],
            'content.ar' => ['required', 'string'],

            'category_id' => ['required', 'uuid', 'exists:categories,uuid'],

            'status' => ['nullable', Rule::in(['draft', 'pending_review'])],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['uuid', 'exists:tags,uuid'],

            'cover_image' => ['nullable', 'image', 'max:5120', 'mimes:jpeg,png,jpg,webp'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing(['status' => 'draft']);
    }
}
