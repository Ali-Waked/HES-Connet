<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoryRequest extends FormRequest
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
                Rule::exists('categories', 'id')->where('type', 'story'),
            ],
            'title' => ['required', 'array'],
            'title.ar' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],

            'content' => ['required', 'array'],
            'content.ar' => ['required', 'string'],
            'content.en' => ['nullable', 'string'],

            'cover_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'is_fundraising' => ['nullable', 'boolean'],

            'target_amount' => ['exclude_unless:is_fundraising,true', 'numeric', 'min:1'],
        ];
    }
}
