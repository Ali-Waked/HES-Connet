<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Provider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * @return bool
     */
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'provider' => ['sometimes', 'string', new Enum(Provider::class)],
            'provider_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
