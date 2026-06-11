<?php

namespace App\Http\Requests\Facility;

use App\Enums\FacilityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'array'],
            'name.en' => ['sometimes', 'required', 'string', 'max:255'],
            'name.ar' => ['sometimes', 'required', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],

            'facility_type' => [
                'sometimes', 'required',
                new Enum(FacilityType::class),
            ],

            'status' => [
                'sometimes', 'required',
                Rule::in([
                    'pending',
                    'active',
                    'inactive',
                    'temporarily_closed',
                    'permanently_closed',
                ]),
            ],

            'approval_status' => [
                'sometimes', 'required',
                Rule::in([
                    'pending',
                    'approved',
                    'rejected',
                    'suspended',
                ]),
            ],

            'organization_id' => [
                'sometimes', 'required',
                'exists:organizations,uuid',
            ],

            'parent_id' => [
                'nullable',
                'exists:facilities,uuid',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ];
    }
}
