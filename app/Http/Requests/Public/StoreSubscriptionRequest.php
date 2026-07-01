<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Enums\LocaleType;
use App\Enums\SubscriptionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'locale' => ['required', 'string', 'in:'.implode(',', LocaleType::values())],
            'types' => ['required', 'array', 'min:1'],
            'types.*' => ['required', 'string', 'in:'.SubscriptionTypeEnum::imploded()],
        ];
    }
}
