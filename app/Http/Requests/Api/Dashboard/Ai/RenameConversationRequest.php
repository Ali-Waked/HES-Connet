<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Dashboard\Ai;

use Illuminate\Foundation\Http\FormRequest;

class RenameConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasSystemRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:60'],
        ];
    }
}
