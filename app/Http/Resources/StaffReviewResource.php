<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,

            'rating' => $this->rating,

            'content' => $this->content,

            'created_at' => $this->created_at,

            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->user?->name,
            ],

            'appointment' => [
                'uuid' => $this->appointment?->uuid,
                'start_at' => $this->appointment?->start_at,
                'end_at' => $this->appointment?->end_at,
            ],

            'reply' => $this->reply
                ? [
                    'id' => $this->reply->id,
                    'reply' => $this->reply->reply,
                    'created_at' => $this->reply->created_at,
                ]
                : null,

            'is_replied' => $this->reply !== null,
        ];
    }
}
