<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'doctor' => $this->whenLoaded('doctor', fn () => [
                'uuid' => $this->doctor->uuid,
                'name' => $this->doctor->user->getTranslations('name'),
            ]),
            'items' => PrescriptionItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
