<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{
    use HasTranslatableFields;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/admin/*') || ($request->user()?->role?->name === 'admin');

        return array_merge([
            'id' => $this->id,
            'uuid' => $this->uuid,
            'facility_type' => $this->facility_type,
            'status' => $this->status->value,
            'approval_status'=>$this->approval_status,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'parent' => new FacilityResource($this->whenLoaded('parent')),
            'children' => FacilityResource::collection($this->whenLoaded('children')),
            'images' => FacilityImageResource::collection($this->whenLoaded('facilityImages')),
            'documents' => FacilityDocumentResource::collection($this->whenLoaded('facilityDocuments')),
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),
        ], $this->mapTranslatable(['name'], $isAdmin));
    }
}
