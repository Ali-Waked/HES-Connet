<?php

declare(strict_types=1);

namespace App\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('pages', 'slug')->ignore($this->route('page')),
            ],
            'title' => ['sometimes', 'required', 'array'],
            'title.en' => ['sometimes', 'required', 'string', 'max:255'],
            'title.ar' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'array'],
            'content.en' => ['sometimes', 'required', 'string'],
            'content.ar' => ['sometimes', 'required', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published'],
        ];
    }
}
