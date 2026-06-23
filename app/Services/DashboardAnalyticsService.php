<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Facility;
use App\Models\MedicationRequest;
use App\Models\Medicine;
use App\Models\PrescriptionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    public function getDashboard(): array
    {
        return [
            'summary' => $this->getSummary(),
            'monthly_trend' => $this->getMonthlyTrend(),
            'status_distribution' => $this->getStatusDistribution(),
            'top_pharmacies' => $this->getTopPharmacies(),
            'most_requested_medicines' => $this->getMostRequestedMedicines(),
            'recent_requests' => $this->getRecentRequests(),
        ];
    }

    public function getSummary(): array
    {
        $totals = MedicationRequest::query()
            ->selectRaw("
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests
            ")
            ->first();

        $totalApprovedOrRejected = ($totals->approved_requests + $totals->rejected_requests);
        $approvalRate = $totalApprovedOrRejected > 0
            ? round(($totals->approved_requests / $totalApprovedOrRejected) * 100, 1)
            : 0;

        return [
            'total_requests' => (int) $totals->total_requests,
            'pending_requests' => (int) $totals->pending_requests,
            'approved_requests' => (int) $totals->approved_requests,
            'rejected_requests' => (int) $totals->rejected_requests,
            'approval_rate' => $approvalRate,
        ];
    }

    public function getMonthlyTrend(): Collection
    {
        $months = collect();
        $now = now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months->push($date->format('Y-m'));
        }

        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $raw = MedicationRequest::query()
            ->selectRaw("{$dateFormat} as month, COUNT(*) as total")
            ->where('created_at', '>=', $now->copy()->subMonths(12)->startOfMonth())
            ->groupBy(DB::raw($dateFormat))
            ->orderBy('month')
            ->pluck('total', 'month');

        return $months->map(fn (string $m) => [
            'month' => Carbon::createFromFormat('Y-m', $m)->format('M'),
            'total' => (int) ($raw[$m] ?? 0),
        ]);
    }

    public function getStatusDistribution(): Collection
    {
        return collect(['pending', 'approved', 'rejected'])->map(fn (string $status) => [
            'status' => $status,
            'count' => MedicationRequest::where('status', $status)->count(),
        ]);
    }

    public function getTopPharmacies(): Collection
    {
        $aggregates = DB::table('medication_requests')
            ->selectRaw('
                facility_id,
                COUNT(*) as requests_count,
                SUM(CASE WHEN status = \'approved\' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = \'rejected\' THEN 1 ELSE 0 END) as rejected_count
            ')
            ->groupBy('facility_id')
            ->orderByDesc('requests_count')
            ->limit(10)
            ->get();

        if ($aggregates->isEmpty()) {
            return collect();
        }

        $facilityIds = $aggregates->pluck('facility_id');
        $facilities = Facility::whereIn('id', $facilityIds)->get()->keyBy('id');

        return $aggregates->map(fn ($row) => [
            'facility_uuid' => $facilities[$row->facility_id]->uuid,
            'facility_name' => $facilities[$row->facility_id]->getTranslations('name'),
            'requests_count' => (int) $row->requests_count,
            'approval_rate' => $this->calculateRate(
                (int) $row->approved_count,
                (int) $row->rejected_count
            ),
        ]);
    }

    public function getMostRequestedMedicines(): Collection
    {
        $aggregates = DB::table('medication_requests')
            ->join('prescriptions', 'medication_requests.prescription_id', '=', 'prescriptions.id')
            ->join('prescription_items', 'prescriptions.id', '=', 'prescription_items.prescription_id')
            ->selectRaw('
                prescription_items.medicine_id,
                COUNT(*) as requests_count
            ')
            ->groupBy('prescription_items.medicine_id')
            ->orderByDesc('requests_count')
            ->limit(10)
            ->get();

        if ($aggregates->isEmpty()) {
            return collect();
        }

        $medicineIds = $aggregates->pluck('medicine_id');
        $medicines = Medicine::whereIn('id', $medicineIds)->get()->keyBy('id');

        return $aggregates->map(fn ($row) => [
            'medicine_uuid' => $medicines[$row->medicine_id]->uuid,
            'medicine_name' => $medicines[$row->medicine_id]->getTranslations('name'),
            'requests_count' => (int) $row->requests_count,
        ]);
    }

    public function getRecentRequests(): Collection
    {
        return MedicationRequest::with([
            'patient.user:id,name',
            'facility:id,uuid,name',
        ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (MedicationRequest $request) => [
                'uuid' => $request->uuid,
                'patient_name' => $request->patient?->user?->getTranslations('name'),
                'pharmacy_name' => $request->facility?->getTranslations('name'),
                'status' => $request->status,
                'created_at' => $request->created_at,
            ]);
    }

    private function calculateRate(int $approved, int $rejected): float
    {
        $total = $approved + $rejected;

        return $total > 0 ? round(($approved / $total) * 100, 1) : 0;
    }
}
