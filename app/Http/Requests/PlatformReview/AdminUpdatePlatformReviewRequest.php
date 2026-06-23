<?php

declare(strict_types=1);

namespace App\Http\Requests\PlatformReview;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdatePlatformReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', 'string', 'in:pending,approved,rejected'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }
}
