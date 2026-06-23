<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FacilityReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FacilityReviewService
{
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?string $isVisible = null,
        ?string $facilityUuid = null,
    ): LengthAwarePaginator {
        return FacilityReview::query()
            ->with([
                'facility',
                'patient.user',
            ])
            ->when($search, fn ($query) => $query->whereHas('patient.user', fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
            ))
            ->when($isVisible !== null, fn ($query) => $query->where('is_visible', $isVisible === 'visible'))
            ->when($facilityUuid, fn ($query) => $query->whereHas('facility', fn ($q) => $q
                ->where('uuid', $facilityUuid)
            ))
            ->latest()
            ->paginate($perPage);
    }

    public function show(FacilityReview $facilityReview): FacilityReview
    {
        return $facilityReview->load([
            'facility',
            'patient.user',
        ]);
    }
}
