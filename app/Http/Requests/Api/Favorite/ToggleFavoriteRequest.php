<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Favorite;

use App\Enums\FavoriteType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ToggleFavoriteRequest extends FormRequest
{
    public function rules(): array
    {
        info($this->all());

        return [
            'type' => ['required', new Enum(FavoriteType::class)],
            'id' => ['required', 'uuid'],
        ];
    }
}
