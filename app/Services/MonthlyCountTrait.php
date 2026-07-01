<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait MonthlyCountTrait
{
    protected function monthlyCount($model, ?int $facilityId = null, ?string $facilityColumn = null): Collection
    {
        $now = now();
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months->push($date->format('Y-m'));
        }

        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $query = $model->where('created_at', '>=', $now->copy()->subMonths(12)->startOfMonth());

        if ($facilityId !== null && $facilityColumn !== null) {
            $query->where($facilityColumn, $facilityId);
        }

        $raw = $query
            ->selectRaw("{$dateFormat} as month, COUNT(*) as total")
            ->groupBy(DB::raw($dateFormat))
            ->orderBy('month')
            ->pluck('total', 'month');

        return $months->map(fn (string $m) => [
            'label' => Carbon::createFromFormat('Y-m', $m)->format('M'),
            'value' => (int) ($raw[$m] ?? 0),
        ]);
    }

    protected function growthPercentage($model, ?int $facilityId = null, ?string $facilityColumn = null): float
    {
        $now = now();
        $currentPeriod = (clone $now)->startOfMonth();
        $previousPeriod = (clone $now)->subMonth()->startOfMonth();

        $applyFilter = function ($query) use ($facilityId, $facilityColumn) {
            if ($facilityId !== null && $facilityColumn !== null) {
                $query->where($facilityColumn, $facilityId);
            }

            return $query;
        };

        $current = $applyFilter($model->where('created_at', '>=', $currentPeriod))->count();
        $previous = $applyFilter($model->whereBetween('created_at', [$previousPeriod, $currentPeriod]))->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
