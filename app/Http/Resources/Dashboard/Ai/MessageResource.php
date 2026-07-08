<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
            'tool_name' => $this->tool_name,
            'tool_arguments' => $this->tool_arguments,
            'tool_result' => $this->tool_result,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
