<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserManagementService
{
    public function getStats(): array
    {

        return [
            'total_users' => User::count(),
            'total_staff' => Staff::count(),
            'total_patients' => Patient::count(),
            'online_now' => User::where('last_seen_at', '>=', now()->subMinutes(5))->count(),
        ];
    }

    public function paginateStaff(
        int $perPage = 15,
        ?string $search = null,
        ?string $specialization = null,
        ?string $status = null,
    ): LengthAwarePaginator {

        return Staff::query()
            ->with('user')
            ->when($search, fn ($query) => $query->search($search))
            ->when($specialization, fn ($query) => $query->ofSpecialization($specialization))
            ->when($status, fn ($query) => $query->ofStatus($status))
            ->latest('id')
            ->paginate($perPage);
    }

    public function paginatePatients(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
    ): LengthAwarePaginator {

        return Patient::query()
            ->with(['user', 'user.profile'])
            ->when($search, fn ($query) => $query->search($search))
            ->when($status, fn ($query) => $query->ofStatus($status))
            ->latest('id')
            ->paginate($perPage);
    }
}
