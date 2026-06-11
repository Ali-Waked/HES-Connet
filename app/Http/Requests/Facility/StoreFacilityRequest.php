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
        'name' => ['required','array'],
        'name.en' => ['required', 'string', 'max:255'],
        'name.ar' => ['required', 'string', 'max:255'],
        
        'description' => ['nullable','array'],
        'description.en' => ['nullable', 'string'],
        'description.ar' => ['nullable', 'string'],

        'facility_type' => [
            'required',
            new Enum(FacilityType::class),
        ],

        'status' => [
            'required',
            Rule::in([
                'pending',
                'active',
                'inactive',
                'temporarily_closed',
                'permanently_closed',
            ]),
        ],

        'approval_status' => [
            'required',
            Rule::in([
                'pending',
                'approved',
                'rejected',
                'suspended',
            ]),
        ],

        'organization_id' => [
            'required',
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