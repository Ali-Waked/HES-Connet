<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:roles,name,' . $this->route('role')],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }
}
