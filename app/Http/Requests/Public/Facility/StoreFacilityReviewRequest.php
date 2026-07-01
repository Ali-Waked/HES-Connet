<?php

declare(strict_types=1);

namespace App\Http\Requests\Public\Facility;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
