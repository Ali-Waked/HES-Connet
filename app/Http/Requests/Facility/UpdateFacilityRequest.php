<?php

namespace App\Http\Requests\Facility;

use App\Enums\FacilityType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'latitude' => [
                'sometimes',
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'sometimes',
                'required',
                'numeric',
                'between:-180,180',
            ],

            'facility_type' => [
                'sometimes',
                'required',
                'string',
                 new Enum(FacilityType::class),
            ],

            'organization_id' => [
                'sometimes',
                'required',
                'exists:organizations,uuid',
            ],

            'parent_id' => [
                'nullable',
                'exists:facilities,uuid',
            ],
        ];
    }
}