<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'array'],
            'name.en' => ['required_if:name', 'string', 'max:255'],
            'name.ar' => ['required_if:name', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'unique:users,email,'.$this->route('user')],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'roles' => ['sometimes', 'required', 'array'],
            'roles.*' => ['required', 'exists:roles,uuid'],
        ];
    }
}
