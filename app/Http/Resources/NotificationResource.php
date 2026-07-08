<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->data['type'] ?? null,
            'title' => $this->data['title'] ?? null,
            'message' => $this->data['message'] ?? null,
            'icon' => $this->data['icon'] ?? null,
            'color' => $this->data['color'] ?? null,
            'group' => $this->data['group'] ?? null,
            'action_url' => $this->data['action_url'] ?? null,
            'action_type' => $this->data['action_type'] ?? null,
            'entity_uuid' => $this->data['entity_uuid'] ?? null,
            'data' => $this->data,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
