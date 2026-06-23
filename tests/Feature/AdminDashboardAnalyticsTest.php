<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\MedicationRequest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        $this->artisan('migrate', ['--force' => true]);
    }

    private function createSuperAdmin(): User
    {
        $role = Role::create([
            'name' => ['en' => 'super_admin', 'ar' => 'مشرف عام'],
            'slug' => 'super_admin',
            'uuid' => Str::uuid(),
        ]);

        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);

        return $user;
    }

    private function createFacilityOwner(): User
    {
        $role = Role::create([
            'name' => ['en' => 'facility_owner', 'ar' => 'مالك منشأة'],
            'slug' => 'facility_owner',
            'uuid' => Str::uuid(),
        ]);

        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);

        return $user;
    }

    private function createFacility(string $name): Facility
    {
        return Facility::create([
            'uuid' => Str::uuid(),
            'name' => ['en' => $name, 'ar' => ''],
            'facility_type' => 'pharmacy',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);
    }

    private function createPatientWithUser(string $name): Patient
    {
        $user = User::factory()->create([
            'name' => ['en' => $name, 'ar' => ''],
        ]);

        return Patient::create(['user_id' => $user->id]);
    }

    private function createMedicine(string $name): Medicine
    {
        return Medicine::create([
            'name' => ['en' => $name, 'ar' => ''],
        ]);
    }

    private function createPrescriptionWithItem(int $medicineId, int $patientId): Prescription
    {
        $prescription = Prescription::create([
            'appointment_id' => null,
            'doctor_id' => null,
            'notes' => 'Test prescription',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_id' => $medicineId,
            'dosage' => '500mg',
            'duration' => '7 days',
        ]);

        return $prescription;
    }

    private function createMedicationRequest(
        int $patientId,
        int $facilityId,
        int $prescriptionId,
        string $status,
        string $createdAt = null
    ): MedicationRequest {
        $data = [
            'patient_id' => $patientId,
            'facility_id' => $facilityId,
            'prescription_id' => $prescriptionId,
            'status' => $status,
        ];

        if ($createdAt) {
            $data['created_at'] = $createdAt;
        }

        return MedicationRequest::create($data);
    }

    private function seedTestData(): array
    {
        $facility1 = $this->createFacility('Pharmacy A');
        $facility2 = $this->createFacility('Pharmacy B');

        $patient1 = $this->createPatientWithUser('John Doe');
        $patient2 = $this->createPatientWithUser('Jane Smith');

        $medicine1 = $this->createMedicine('Panadol');
        $medicine2 = $this->createMedicine('Aspirin');
        $medicine3 = $this->createMedicine('Amoxicillin');

        $prescription1 = $this->createPrescriptionWithItem($medicine1->id, $patient1->id);
        $prescription2 = $this->createPrescriptionWithItem($medicine2->id, $patient1->id);
        $prescription3 = $this->createPrescriptionWithItem($medicine1->id, $patient2->id);
        $prescription4 = $this->createPrescriptionWithItem($medicine3->id, $patient2->id);

        $this->createMedicationRequest($patient1->id, $facility1->id, $prescription1->id, 'approved', now()->subMonths(1));
        $this->createMedicationRequest($patient1->id, $facility1->id, $prescription2->id, 'approved');
        $this->createMedicationRequest($patient2->id, $facility1->id, $prescription3->id, 'pending');
        $this->createMedicationRequest($patient2->id, $facility2->id, $prescription4->id, 'rejected');

        return compact(
            'facility1', 'facility2',
            'patient1', 'patient2',
            'medicine1', 'medicine2', 'medicine3',
        );
    }

    // ─── Authorization ────────────────────────────────────────────────────

    public function test_super_admin_can_access_medication_analytics(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_medication_analytics(): void
    {
        $user = $this->createFacilityOwner();

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $response->assertForbidden();
    }

    public function test_unauthenticated_cannot_access(): void
    {
        $response = $this->getJson('/api/admin/prescriptions/analytics');

        $response->assertUnauthorized();
    }

    // ─── Summary ──────────────────────────────────────────────────────────

    public function test_summary_returns_correct_counts(): void
    {
        $admin = $this->createSuperAdmin();
        $this->seedTestData();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $response->assertOk()
            ->assertJsonPath('summary.total_requests', 4)
            ->assertJsonPath('summary.pending_requests', 1)
            ->assertJsonPath('summary.approved_requests', 2)
            ->assertJsonPath('summary.rejected_requests', 1);
    }

    public function test_summary_approval_rate_is_correct(): void
    {
        $admin = $this->createSuperAdmin();
        $this->seedTestData();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $response->assertOk()
            ->assertJsonPath('summary.approval_rate', 66.7);
    }

    public function test_summary_zero_approval_rate_when_no_requests(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $response->assertOk()
            ->assertJsonPath('summary.total_requests', 0)
            ->assertJsonPath('summary.approval_rate', 0);
    }

    // ─── Status Distribution ──────────────────────────────────────────────

    public function test_status_distribution_returns_all_statuses(): void
    {
        $admin = $this->createSuperAdmin();
        $this->seedTestData();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $distribution = collect($response->json('status_distribution'));

        $this->assertCount(3, $distribution);
        $this->assertSame(1, $distribution->firstWhere('status', 'pending')['count']);
        $this->assertSame(2, $distribution->firstWhere('status', 'approved')['count']);
        $this->assertSame(1, $distribution->firstWhere('status', 'rejected')['count']);
    }

    // ─── Top Pharmacies ───────────────────────────────────────────────────

    public function test_top_pharmacies_returns_correct_data(): void
    {
        $admin = $this->createSuperAdmin();
        $this->seedTestData();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $pharmacies = $response->json('top_pharmacies');

        $this->assertCount(2, $pharmacies);
        $this->assertSame('Pharmacy A', $pharmacies[0]['facility_name']['en']);
        $this->assertSame(3, $pharmacies[0]['requests_count']);
        $this->assertSame(66.7, $pharmacies[0]['approval_rate']);
    }

    public function test_top_pharmacies_ordered_by_count_desc(): void
    {
        $admin = $this->createSuperAdmin();
        $this->seedTestData();
        $facility3 = $this->createFacility('Pharmacy C');

        $patient = $this->createPatientWithUser('Extra Patient');
        $medicine = $this->createMedicine('Test Med');
        $prescription = $this->createPrescriptionWithItem($medicine->id, $patient->id);

        for ($i = 0; $i < 5; $i++) {
            $this->createMedicationRequest($patient->id, $facility3->id, $prescription->id, 'approved');
        }

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $pharmacies = $response->json('top_pharmacies');

        $this->assertSame('Pharmacy C', $pharmacies[0]['facility_name']['en']);
        $this->assertSame(5, $pharmacies[0]['requests_count']);
    }

    // ─── Most Requested Medicines ─────────────────────────────────────────

    public function test_most_requested_medicines_returns_correct_data(): void
    {
        $admin = $this->createSuperAdmin();
        $this->seedTestData();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $medicines = $response->json('most_requested_medicines');

        $this->assertCount(3, $medicines);
        $panadol = collect($medicines)->firstWhere('medicine_name.en', 'Panadol');
        $this->assertNotNull($panadol);
        $this->assertSame(2, $panadol['requests_count']);
    }

    // ─── Recent Requests ──────────────────────────────────────────────────

    public function test_recent_requests_returns_latest_first(): void
    {
        $admin = $this->createSuperAdmin();

        $facility = $this->createFacility('Test Pharmacy');
        $patient = $this->createPatientWithUser('Test Patient');
        $medicine = $this->createMedicine('Test Med');

        $prescription = $this->createPrescriptionWithItem($medicine->id, $patient->id);

        $this->createMedicationRequest($patient->id, $facility->id, $prescription->id, 'pending');
        $this->createMedicationRequest($patient->id, $facility->id, $prescription->id, 'approved');

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $recent = $response->json('recent_requests');

        $this->assertCount(2, $recent);
        $this->assertSame('approved', $recent[0]['status']);
        $this->assertSame('pending', $recent[1]['status']);
    }

    public function test_recent_requests_includes_patient_and_pharmacy(): void
    {
        $admin = $this->createSuperAdmin();

        $facility = $this->createFacility('City Pharmacy');
        $patient = $this->createPatientWithUser('Alice Brown');
        $medicine = $this->createMedicine('Test Med');

        $prescription = $this->createPrescriptionWithItem($medicine->id, $patient->id);
        $request = $this->createMedicationRequest($patient->id, $facility->id, $prescription->id, 'approved');

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $recent = $response->json('recent_requests');

        $this->assertCount(1, $recent);
        $this->assertSame($request->uuid, $recent[0]['uuid']);
        $this->assertSame('Alice Brown', $recent[0]['patient_name']['en']);
        $this->assertSame('City Pharmacy', $recent[0]['pharmacy_name']['en']);
        $this->assertSame('approved', $recent[0]['status']);
    }

    public function test_recent_requests_limited_to_10(): void
    {
        $admin = $this->createSuperAdmin();

        $facility = $this->createFacility('Big Pharmacy');
        $patient = $this->createPatientWithUser('Bulk Patient');
        $medicine = $this->createMedicine('Test Med');

        $prescription = $this->createPrescriptionWithItem($medicine->id, $patient->id);

        for ($i = 0; $i < 15; $i++) {
            $this->createMedicationRequest($patient->id, $facility->id, $prescription->id, 'pending');
        }

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $recent = $response->json('recent_requests');

        $this->assertCount(10, $recent);
    }

    // ─── Monthly Trend ────────────────────────────────────────────────────

    public function test_monthly_trend_returns_12_months(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $trend = $response->json('monthly_trend');

        $this->assertCount(12, $trend);
    }

    public function test_monthly_trend_includes_data_for_months_with_requests(): void
    {
        $admin = $this->createSuperAdmin();

        $facility = $this->createFacility('Test Pharm');
        $patient = $this->createPatientWithUser('Test User');
        $medicine = $this->createMedicine('Test Med');
        $prescription = $this->createPrescriptionWithItem($medicine->id, $patient->id);

        $this->createMedicationRequest(
            $patient->id, $facility->id, $prescription->id, 'approved',
            now()->subMonths(2)->startOfMonth()->toDateTimeString()
        );

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $trend = $response->json('monthly_trend');
        $targetMonth = now()->subMonths(2)->format('M');
        $entry = collect($trend)->firstWhere('month', $targetMonth);

        $this->assertNotNull($entry, "Month {$targetMonth} not found in trend");
        $this->assertSame(1, $entry['total']);
    }

    // ─── Full Response Structure ──────────────────────────────────────────

    public function test_response_has_correct_structure(): void
    {
        $admin = $this->createSuperAdmin();
        $this->seedTestData();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/admin/prescriptions/analytics');

        $response->assertOk()
            ->assertJsonStructure([
                'summary' => [
                    'total_requests',
                    'pending_requests',
                    'approved_requests',
                    'rejected_requests',
                    'approval_rate',
                ],
                'monthly_trend' => [
                    '*' => ['month', 'total'],
                ],
                'status_distribution' => [
                    '*' => ['status', 'count'],
                ],
                'top_pharmacies' => [
                    '*' => [
                        'facility_uuid',
                        'facility_name',
                        'requests_count',
                        'approval_rate',
                    ],
                ],
                'most_requested_medicines' => [
                    '*' => [
                        'medicine_uuid',
                        'medicine_name',
                        'requests_count',
                    ],
                ],
                'recent_requests' => [
                    '*' => [
                        'uuid',
                        'patient_name',
                        'pharmacy_name',
                        'status',
                        'created_at',
                    ],
                ],
            ]);
    }
}
