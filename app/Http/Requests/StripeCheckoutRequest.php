<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StripeCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'donation_id' => ['required', 'exists:donations,uuid'],
            'success_url' => ['required', 'url'],
            'cancel_url' => ['required', 'url'],
        ];
    }
}
