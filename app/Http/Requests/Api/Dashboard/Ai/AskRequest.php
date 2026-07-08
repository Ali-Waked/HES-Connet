<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Dashboard\Ai;

use Illuminate\Foundation\Http\FormRequest;

class AskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasSystemRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'conversation_uuid' => ['nullable', 'string', 'exists:ai_conversations,uuid'],
            'message' => ['required', 'string', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'conversation_uuid.exists' => 'Conversation not found.',
        ];
    }
}
