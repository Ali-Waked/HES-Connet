<?php

declare(strict_types=1);

namespace App\Http\Requests\Patient;

use App\Enums\GenderType;
use App\Enums\LocaleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'array'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'unique:users,email,'.$this->user()->id],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'gender' => ['sometimes', 'nullable', 'string',  new Enum(GenderType::class)],
            'city_id' => ['sometimes', 'nullable', 'uuid', 'exists:cities,uuid'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'cover_image' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'locale' => ['sometimes', 'required', new Enum(LocaleType::class)],
        ];
    }
}
