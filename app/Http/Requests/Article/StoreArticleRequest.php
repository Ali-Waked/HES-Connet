<?php

namespace App\Http\Requests\Article;

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

            'category_id' => ['required', 'uuid', Rule::exists('categories', 'uuid')->where('type', 'article')],

            'status' => ['required', Rule::in(['draft', 'pending_review', 'published', 'archived', 'rejected'])],

            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['uuid'],

            'cover_image' => ['required', 'image', 'max:5120'],
        ];
    }
}
