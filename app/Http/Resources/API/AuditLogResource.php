<?php

declare(strict_types=1);

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'table_name' => $this->table_name,
            'record_id' => $this->record_id,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at,

            'user' => $this->whenLoaded('user', fn () => [
                'uuid' => $this->user->uuid,
                'name' => $this->user->getTranslations('name'),
            ]),
        ];
    }
}
