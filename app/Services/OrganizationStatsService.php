<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Facility;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class OrganizationStatsService
{
    public function getStats(): array
    {
        $types = Organization::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $typeDefaults = [
            'government' => 0,
            'un_agency' => 0,
            'international_ngo' => 0,
            'local_ngo' => 0,
            'private' => 0,
        ];

        return [
            'total_organizations' => array_sum($types),
            'organizations_by_type' => array_merge($typeDefaults, $types),
            'total_users' => DB::table('organization_user')->distinct()->count('user_id'),
            'total_facilities' => Facility::whereNotNull('organization_id')->count(),
        ];
    }
}
