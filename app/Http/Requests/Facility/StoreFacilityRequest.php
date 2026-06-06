<?php

namespace App\Http\Requests\Facility;

use App\Enums\FacilityType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'facility_type' => [
                'required',
                'string',
               new Enum(FacilityType::class)
            ],

            'organization_id' => [
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