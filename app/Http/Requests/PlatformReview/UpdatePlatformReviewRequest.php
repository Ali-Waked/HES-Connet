<?php

declare(strict_types=1);

namespace App\Http\Requests\PlatformReview;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'required', 'integer', 'min:1', 'max:5'],
            'comment' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
