<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dosage' => $this->dosage,
            'frequency' => $this->frequency,
            'duration' => $this->duration,
            'route' => $this->route,
            'instructions' => $this->instructions,
            'medicine' => $this->whenLoaded('medicine', fn () => [
                'uuid' => $this->medicine->uuid,
                'name' => $this->medicine->getTranslations('name'),
            ]),
        ];
    }
}
