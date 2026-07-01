<?php

declare(strict_types=1);

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
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
            'key' => ['sometimes', 'required', 'string', 'max:255', 'unique:permissions,key,'.$this->route('permission')],
            'name' => ['sometimes', 'array'],
            'name.en' => ['required_with:name', 'string'],
            'name.ar' => ['required_with:name', 'string'],
            'description' => ['sometimes', 'array'],
            'description.en' => ['required_with:description', 'string'],
            'description.ar' => ['required_with:description', 'string'],
        ];
    }
}
