<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Enums\SubscriptionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionTypesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'types' => ['required', 'array', 'min:1'],
            'types.*' => ['required', 'string', 'in:'.SubscriptionTypeEnum::imploded()],
        ];
    }
}
