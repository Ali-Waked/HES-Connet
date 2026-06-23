<?php

declare(strict_types=1);

namespace App\Http\Requests\Facility;

use App\Enums\FacilityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreFacilityRequest extends FormRequest
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
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
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

            'organization_id' => ['nullable', 'uuid', 'exists:organizations,uuid'],
            'owner_id' => ['nullable', 'uuid', 'exists:users,uuid'],
            'parent_id' => ['nullable', 'uuid', 'exists:facilities,uuid'],
            'city_id' => ['nullable', 'uuid', 'exists:cities,uuid'],
            'head_staff_id' => ['nullable', 'uuid', 'exists:staff,uuid'],

            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            'cover_image' => ['required', 'image', 'max:5120'],

            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:5120'],

            'is_featured' => ['sometimes', 'required', 'in:0,1'],

            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:5120'],
        ];
    }
}