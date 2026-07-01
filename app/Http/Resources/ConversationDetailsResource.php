<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'participants' => $this->whenLoaded('participants', fn () => $this->participants->map(fn ($user) => [
                'uuid' => $user->uuid,
                'name' => $user->getTranslations('name'),
                'avatar' => $user->avatar,
                'last_read_at' => $user->pivot->last_read_at,
            ])
            ),
        ];
    }
}
