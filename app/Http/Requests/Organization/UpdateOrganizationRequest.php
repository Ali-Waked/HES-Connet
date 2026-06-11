<?php

namespace App\Http\Requests\Organization;

use App\Enums\OrganizationType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'array'],
            'name.en' => ['sometimes', 'string', 'max:255'],
            'name.ar' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'type' => ['sometimes', new Enum(OrganizationType::class)],
        ];
    }
}
