<?php

declare(strict_types=1);

namespace App\Http\Requests\ContactMessage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactMessageStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:new,read,replied'],
        ];
    }
}
