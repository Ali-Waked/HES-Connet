<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'required', 'array'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user, 'uuid')],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'max:128'],
            'phone' => ['nullable', 'string', 'max:20'],
            'locale' => ['nullable', 'string', Rule::in(['en', 'ar'])],
            'is_active' => ['boolean'],
            'roles' => ['sometimes', 'required', 'array'],
            'roles.*' => ['required', 'exists:roles,uuid'],
            'city_id' => ['nullable', 'exists:cities,uuid'],
        ];
    }
}
