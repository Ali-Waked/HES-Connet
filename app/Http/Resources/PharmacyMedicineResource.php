<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyMedicineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'stock' => $this->stock,
            'is_available' => $this->is_available,
            'price' => $this->price,

            'medicine' => [
                'uuid' => $this->medicine->uuid,
                'name' => $this->medicine->name,
                'image_url' => $this->medicine->image_url,
            ],
        ];
    }
}
