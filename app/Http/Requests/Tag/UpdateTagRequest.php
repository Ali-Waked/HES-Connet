<?php

namespace App\Http\Requests\Tag;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','required','array'],
            'name.en' => ['sometimes','required', 'string', 'max:255'],
            'name.ar' => ['sometimes','required', 'string', 'max:255'],
        ];
    }
}
