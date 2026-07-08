<?php

declare(strict_types=1);

namespace App\Http\Requests\Conversation;

use Illuminate\Foundation\Http\FormRequest;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'message' => ['sometimes', 'required', 'string', 'max:10000'],
            'participant_ids' => ['sometimes', 'required', 'array', 'min:1'],
            'participant_ids.*' => ['required', 'exists:users,uuid'],
        ];
    }
}
