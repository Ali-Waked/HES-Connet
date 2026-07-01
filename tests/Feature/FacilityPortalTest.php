<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\AppointmentStatus;
use App\Enums\FacilityApprovalStatus;
use App\Enums\FacilityStatus;
use App\Enums\FacilityType;
use App\Models\Appointment;
use App\Models\Facility;
use App\Models\FacilityReview;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FacilityPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        $this->artisan('migrate', ['--force' => true]);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    private function createPermission(string $key): Permission
    {
        return Permission::create([
            'key' => $key,
            'uuid' => Str::uuid(),
            'name' => ['en' => $key, 'ar' => ''],
            'description' => ['en' => '', 'ar' => ''],
        ]);
    }

    private function createRoleWithPermissions(string $roleName, array $permissionKeys): Role
    {
        $role = Role::create([
            'name' => ['en' => $roleName, 'ar' => ''],
            'slug' => $roleName,
            'scope' => 'facility',
            'uuid' => Str::uuid(),
        ]);

        $permissions = array_map(fn (string $key) => $this->createPermission($key)->id, $permissionKeys);
        $role->permissions()->sync($permissions);

        return $role;
    }

    private function createUserWithRole(string $roleName, array $permissions = []): User
    {
        $role = $this->createRoleWithPermissions($roleName, $permissions);
        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);

        return $user;
    }

    private function createFacility(User $owner): Facility
    {
        return Facility::create([
            'name' => ['en' => 'Test Facility', 'ar' => 'منشأة اختبار'],
            'facility_type' => FacilityType::CLINIC,
            'status' => FacilityStatus::ACTIVE,
            'approval_status' => FacilityApprovalStatus::APPROVED,
            'created_by' => $owner->id,
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);
    }

    private function createPatient(): Patient
    {
        $user = User::factory()->create();

        return Patient::create([
            'user_id' => $user->id,
            'medical_history' => null,
            'status' => AccountStatus::ACTIVE,
        ]);
    }

    private function createStaff(User $user): Staff
    {
        return Staff::create([
            'user_id' => $user->id,
            'status' => AccountStatus::ACTIVE,
        ]);
    }

    // ─── Dashboard ─────────────────────────────────────────────────────────

    public function test_dashboard_returns_structured_response(): void
    {
        $user = $this->createUserWithRole('facility_owner', [
            'facility_dashboard.view', 'appointments.view', 'patients.view',
            'staff.view', 'departments.view', 'reviews.view', 'medication_requests.view',
        ]);
        $facility = $this->createFacility($user);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'stats',
                'widgets',
                'recent_activity',
                'notifications',
            ]);
    }

    public function test_dashboard_respects_permissions(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['facility_dashboard.view']);
        $facility = $this->createFacility($user);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/dashboard');

        $response->assertOk();
        $this->assertArrayNotHasKey('appointments_today', $response->json('stats'));
    }

    public function test_dashboard_denied_without_permission(): void
    {
        $user = $this->createUserWithRole('facility_owner', []);
        $facility = $this->createFacility($user);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/dashboard');

        $response->assertForbidden();
    }

    // ─── Profile ───────────────────────────────────────────────────────────

    public function test_can_view_profile(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['profile.view']);
        $facility = $this->createFacility($user);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/profile');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['user', 'facility']]);
    }

    public function test_can_update_profile(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['profile.update']);
        $facility = $this->createFacility($user);

        $response = $this->actingAs($user, 'web')
            ->putJson('/api/facility/profile', [
                'name' => ['en' => 'Updated Name', 'ar' => 'اسم محدث'],
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Profile updated successfully.');
    }

    // ─── Patients ──────────────────────────────────────────────────────────

    public function test_can_list_patients(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['patients.view']);
        $facility = $this->createFacility($user);
        $patient = $this->createPatient();
        Appointment::create([
            'staff_id' => $this->createStaff($user)->id,
            'patient_id' => $patient->id,
            'facility_id' => $facility->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'status' => AppointmentStatus::SCHEDULED,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/patients');

        $response->assertOk();
    }

    public function test_patients_denied_without_permission(): void
    {
        $user = $this->createUserWithRole('facility_owner', []);
        $facility = $this->createFacility($user);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/patients');

        $response->assertForbidden();
    }

    // ─── Appointments ──────────────────────────────────────────────────────

    public function test_can_list_appointments(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['appointments.view']);
        $facility = $this->createFacility($user);
        $staff = $this->createStaff($user);
        $patient = $this->createPatient();
        Appointment::create([
            'staff_id' => $staff->id,
            'patient_id' => $patient->id,
            'facility_id' => $facility->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'status' => AppointmentStatus::SCHEDULED,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/appointments');

        $response->assertOk();
    }

    public function test_appointments_scope_to_facility(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['appointments.view']);
        $facility = $this->createFacility($user);
        $otherFacility = $this->createFacility($user);
        $staff = $this->createStaff($user);
        $patient = $this->createPatient();

        Appointment::create([
            'staff_id' => $staff->id,
            'patient_id' => $patient->id,
            'facility_id' => $otherFacility->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'status' => AppointmentStatus::SCHEDULED,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/appointments');

        $response->assertOk();
    }

    // ─── Medicines ─────────────────────────────────────────────────────────

    public function test_can_list_medicines(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['medicines.view']);
        $this->createFacility($user);
        Medicine::create(['name' => ['en' => 'Panadol', 'ar' => 'بانادول']]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/medicines');

        $response->assertOk();
    }

    // ─── Reviews ───────────────────────────────────────────────────────────

    public function test_can_list_reviews(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['reviews.view']);
        $facility = $this->createFacility($user);
        $patient = $this->createPatient();
        FacilityReview::create([
            'facility_id' => $facility->id,
            'patient_id' => $patient->id,
            'rating' => 5,
            'comment' => 'Great!',
            'is_visible' => false,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/reviews');

        $response->assertOk();
    }

    public function test_can_approve_review(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['reviews.approve']);
        $facility = $this->createFacility($user);
        $patient = $this->createPatient();
        $review = FacilityReview::create([
            'facility_id' => $facility->id,
            'patient_id' => $patient->id,
            'rating' => 4,
            'comment' => 'Good',
            'is_visible' => false,
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson("/api/facility/reviews/{$review->id}/approve");

        $response->assertOk()
            ->assertJsonPath('message', 'Review approved successfully.');
        $this->assertTrue($review->refresh()->is_visible);
    }

    public function test_can_reject_review(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['reviews.reject']);
        $facility = $this->createFacility($user);
        $patient = $this->createPatient();
        $review = FacilityReview::create([
            'facility_id' => $facility->id,
            'patient_id' => $patient->id,
            'rating' => 3,
            'comment' => 'Okay',
            'is_visible' => true,
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson("/api/facility/reviews/{$review->id}/reject");

        $response->assertOk()
            ->assertJsonPath('message', 'Review rejected successfully.');
        $this->assertFalse($review->refresh()->is_visible);
    }

    // ─── Articles ──────────────────────────────────────────────────────────

    public function test_can_list_articles(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['articles.view']);
        $this->createFacility($user);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/articles');

        $response->assertOk();
    }

    // ─── Notifications ─────────────────────────────────────────────────────

    public function test_can_list_notifications(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['notifications.view']);
        $this->createFacility($user);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/notifications');

        $response->assertOk();
    }

    // ─── Authorization roles ──────────────────────────────────────────────

    public function test_facility_manager_can_access_facility_portal(): void
    {
        $user = $this->createUserWithRole('facility_manager', ['facility_dashboard.view']);
        $facility = $this->createFacility(
            $this->createUserWithRole('facility_owner', [])
        );
        $staff = $this->createStaff($user);
        $staff->facilities()->attach($facility->id, ['uuid' => Str::uuid()]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/dashboard');

        $response->assertOk();
    }

    public function test_doctor_cannot_access_without_permission(): void
    {
        $user = $this->createUserWithRole('doctor', []);
        $facility = $this->createFacility(
            $this->createUserWithRole('facility_owner', [])
        );
        $staff = $this->createStaff($user);
        $staff->facilities()->attach($facility->id, ['uuid' => Str::uuid()]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/dashboard');

        $response->assertForbidden();
    }

    // ─── Authentication ────────────────────────────────────────────────────

    public function test_unauthenticated_cannot_access_any_endpoint(): void
    {
        $this->getJson('/api/facility/dashboard')->assertUnauthorized();
        $this->getJson('/api/facility/profile')->assertUnauthorized();
        $this->getJson('/api/facility/patients')->assertUnauthorized();
        $this->getJson('/api/facility/appointments')->assertUnauthorized();
        $this->getJson('/api/facility/medicines')->assertUnauthorized();
        $this->getJson('/api/facility/reviews')->assertUnauthorized();
        $this->getJson('/api/facility/articles')->assertUnauthorized();
        $this->getJson('/api/facility/notifications')->assertUnauthorized();
    }

    // ─── Role-based access (role check before permission) ──────────────────

    public function test_unrecognized_role_cannot_access(): void
    {
        $user = $this->createUserWithRole('patient', ['facility_dashboard.view']);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/dashboard');

        $response->assertForbidden();
    }

    public function test_super_admin_can_access_with_facility_id(): void
    {
        $user = $this->createUserWithRole('super_admin', ['facility_dashboard.view']);
        $owner = $this->createUserWithRole('facility_owner', []);
        $facility = $this->createFacility($owner);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/dashboard?facility_id='.$facility->uuid);

        $response->assertOk();
    }

    // ─── Pagination ────────────────────────────────────────────────────────

    public function test_endpoints_return_paginated_results(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['patients.view', 'medicines.view']);
        $facility = $this->createFacility($user);
        $staff = $this->createStaff($user);

        foreach (range(1, 5) as $i) {
            $patient = $this->createPatient();
            Appointment::create([
                'staff_id' => $staff->id,
                'patient_id' => $patient->id,
                'facility_id' => $facility->id,
                'start_at' => now()->addHours($i),
                'end_at' => now()->addHours($i + 1),
                'status' => AppointmentStatus::SCHEDULED,
            ]);
        }

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/patients?per_page=2');

        $response->assertOk();
    }

    // ─── Cross-facility isolation ──────────────────────────────────────────

    public function test_cannot_access_other_facility_patients(): void
    {
        $user = $this->createUserWithRole('facility_owner', ['patients.view']);
        $facility = $this->createFacility($user);
        $otherOwner = $this->createUserWithRole('facility_owner', ['patients.view']);
        $otherFacility = $this->createFacility($otherOwner);
        $otherStaff = $this->createStaff($otherOwner);
        $otherPatient = $this->createPatient();
        Appointment::create([
            'staff_id' => $otherStaff->id,
            'patient_id' => $otherPatient->id,
            'facility_id' => $otherFacility->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'status' => AppointmentStatus::SCHEDULED,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/patients');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }
}
