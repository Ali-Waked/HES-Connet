<?php

namespace App\Http\Requests\JobPost\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class ApproveJobPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
