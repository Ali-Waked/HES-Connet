<?php

namespace App\Http\Requests;

use App\Enums\StoryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\In;

class UpdateStoryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new In([StoryStatus::PENDING->value, StoryStatus::APPROVED->value, StoryStatus::REJECTED->value])],
        ];
    }
}
