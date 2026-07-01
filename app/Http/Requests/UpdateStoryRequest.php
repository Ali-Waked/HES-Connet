<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'required',
                Rule::exists('categories', 'id')->where('type', 'story'),
            ],
            'title' => ['sometimes', 'required', 'array'],
            'title.ar' => ['sometimes', 'required', 'string', 'max:255'],
            'title.en' => ['sometimes', 'nullable', 'string', 'max:255'],

            'content' => ['sometimes', 'required', 'array'],
            'content.ar' => ['sometimes', 'required', 'string'],
            'content.en' => ['sometimes', 'nullable', 'string'],

            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'is_fundraising' => ['sometimes', 'boolean'],

            'target_amount' => ['sometimes', 'required_if:is_fundraising,true', 'numeric', 'min:1'],
        ];
    }
}
