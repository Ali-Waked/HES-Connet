<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'edited_at' => $this->edited_at,
            'sender' => $this->whenLoaded('sender', fn () => [
                'uuid' => $this->sender->uuid,
                'name' => $this->sender->getTranslations('name'),
                'avatar' => $this->sender->avatar,
            ]),
        ];
    }
}
