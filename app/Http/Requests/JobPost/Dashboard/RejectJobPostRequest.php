<?php

namespace App\Http\Requests\JobPost\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class RejectJobPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejected_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
