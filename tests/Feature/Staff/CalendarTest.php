<?php

declare(strict_types=1);

namespace Tests\Feature\Staff;

use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\StaffUnavailability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';

        parent::setUp();
    }

    private function createAuthenticatedUser(): User
    {
        $user = User::factory()->create();

        $staff = Staff::factory()->create(['user_id' => $user->id]);

        return $user->refresh()->load('staff');
    }

    private function createFacilityStaff(Staff $staff, Facility $facility): FacilityStaff
    {
        return FacilityStaff::factory()->create([
            'staff_id' => $staff->id,
            'facility_id' => $facility->id,
        ]);
    }

    public function test_unauthenticated_cannot_access_calendar(): void
    {
        $this->getJson('/api/calendar')->assertUnauthorized();
    }

    public function test_returns_empty_events_when_no_data(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar');

        $response->assertOk()
            ->assertJsonPath('events', []);
    }

    public function test_returns_schedule_events(): void
    {
        $user = $this->createAuthenticatedUser();
        $facility = Facility::factory()->create();
        $fs = $this->createFacilityStaff($user->staff, $facility);

        $monday = Carbon::now()->startOfWeek();
        $startTime = $monday->format('Y-m-d').' 09:00:00';
        $endTime = $monday->format('Y-m-d').' 17:00:00';

        StaffSchedule::create([
            'facility_staff_id' => $fs->id,
            'day_of_week' => (int) $monday->format('w'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar');

        $response->assertOk();
        $events = $response->json('events');
        $this->assertCount(1, $events);
        $this->assertEquals('schedule', $events[0]['type']);
        $this->assertEquals('Working Hours', $events[0]['title']);
        $this->assertEquals('#2563eb', $events[0]['color']);
        $this->assertArrayHasKey('day_of_week', $events[0]);
    }

    public function test_returns_unavailability_events(): void
    {
        $user = $this->createAuthenticatedUser();
        $facility = Facility::factory()->create();
        $fs = $this->createFacilityStaff($user->staff, $facility);

        $now = Carbon::now();
        StaffUnavailability::create([
            'facility_staff_id' => $fs->id,
            'start_at' => $now->copy()->startOfWeek()->addHours(10),
            'end_at' => $now->copy()->startOfWeek()->addHours(12),
            'reason' => 'Sick leave',
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar');

        $response->assertOk();
        $events = $response->json('events');
        $this->assertCount(1, $events);
        $this->assertEquals('unavailability', $events[0]['type']);
        $this->assertEquals('Sick leave', $events[0]['title']);
        $this->assertEquals('#ef4444', $events[0]['color']);
        $this->assertArrayHasKey('reason', $events[0]);
        $this->assertArrayHasKey('status', $events[0]);
    }

    public function test_returns_both_types_sorted_by_start(): void
    {
        $user = $this->createAuthenticatedUser();
        $facility = Facility::factory()->create();
        $fs = $this->createFacilityStaff($user->staff, $facility);

        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek();

        StaffUnavailability::create([
            'facility_staff_id' => $fs->id,
            'start_at' => $weekStart->copy()->addHours(8),
            'end_at' => $weekStart->copy()->addHours(10),
            'reason' => 'Doctor appointment',
        ]);

        StaffSchedule::create([
            'facility_staff_id' => $fs->id,
            'day_of_week' => (int) $weekStart->format('w'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar');

        $response->assertOk();
        $events = $response->json('events');
        $this->assertCount(2, $events);
        $this->assertEquals('unavailability', $events[0]['type'], 'Earlier event should be first');
        $this->assertEquals('schedule', $events[1]['type'], 'Later event should be second');
    }

    public function test_filters_by_facility_uuid(): void
    {
        $user = $this->createAuthenticatedUser();
        $facilityA = Facility::factory()->create();
        $facilityB = Facility::factory()->create();
        $fsA = $this->createFacilityStaff($user->staff, $facilityA);
        $fsB = $this->createFacilityStaff($user->staff, $facilityB);

        $monday = Carbon::now()->startOfWeek();
        StaffSchedule::create([
            'facility_staff_id' => $fsA->id,
            'day_of_week' => (int) $monday->format('w'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        StaffSchedule::create([
            'facility_staff_id' => $fsB->id,
            'day_of_week' => (int) $monday->format('w'),
            'start_time' => '10:00',
            'end_time' => '18:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar?facility_uuid='.$facilityA->uuid);

        $response->assertOk();
        $events = $response->json('events');
        $this->assertCount(1, $events);
        $this->assertEquals($facilityA->uuid, $events[0]['facility']['uuid']);
    }

    public function test_validates_facility_uuid_exists(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar?facility_uuid='.Str::uuid());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['facility_uuid']);
    }

    public function test_weekly_schedule_expands_to_correct_dates(): void
    {
        $user = $this->createAuthenticatedUser();
        $facility = Facility::factory()->create();
        $fs = $this->createFacilityStaff($user->staff, $facility);

        $monday = Carbon::now()->startOfWeek();
        $wednesday = $monday->copy()->addDays(2);
        $friday = $monday->copy()->addDays(4);

        StaffSchedule::create([
            'facility_staff_id' => $fs->id,
            'day_of_week' => (int) $monday->format('w'),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        StaffSchedule::create([
            'facility_staff_id' => $fs->id,
            'day_of_week' => (int) $wednesday->format('w'),
            'start_time' => '13:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        StaffSchedule::create([
            'facility_staff_id' => $fs->id,
            'day_of_week' => (int) $friday->format('w'),
            'start_time' => '10:00',
            'end_time' => '14:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar');

        $response->assertOk();
        $events = $response->json('events');
        $this->assertCount(3, $events);
        $this->assertEquals((int) $monday->format('w'), $events[0]['day_of_week']);
        $this->assertEquals((int) $wednesday->format('w'), $events[1]['day_of_week']);
        $this->assertEquals((int) $friday->format('w'), $events[2]['day_of_week']);
    }

    public function test_uses_custom_week_range(): void
    {
        $user = $this->createAuthenticatedUser();
        $facility = Facility::factory()->create();
        $fs = $this->createFacilityStaff($user->staff, $facility);

        $nextMonday = Carbon::now()->addWeek()->startOfWeek();
        $dayOfWeek = (int) $nextMonday->format('w');

        StaffSchedule::create([
            'facility_staff_id' => $fs->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar?week_start='.$nextMonday->format('Y-m-d').'&week_end='.$nextMonday->copy()->endOfWeek()->format('Y-m-d'));

        $response->assertOk();
        $events = $response->json('events');
        $this->assertCount(1, $events);
    }

    public function test_omits_inactive_schedules(): void
    {
        $user = $this->createAuthenticatedUser();
        $facility = Facility::factory()->create();
        $fs = $this->createFacilityStaff($user->staff, $facility);

        $monday = Carbon::now()->startOfWeek();
        StaffSchedule::create([
            'facility_staff_id' => $fs->id,
            'day_of_week' => (int) $monday->format('w'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'is_active' => false,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar');

        $response->assertOk();
        $events = $response->json('events');
        $this->assertCount(0, $events);
    }

    public function test_returns_events_for_multi_facility_staff(): void
    {
        $user = $this->createAuthenticatedUser();
        $facilityA = Facility::factory()->create();
        $facilityB = Facility::factory()->create();
        $fsA = $this->createFacilityStaff($user->staff, $facilityA);
        $fsB = $this->createFacilityStaff($user->staff, $facilityB);

        $monday = Carbon::now()->startOfWeek();

        StaffSchedule::create([
            'facility_staff_id' => $fsA->id,
            'day_of_week' => (int) $monday->format('w'),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        StaffUnavailability::create([
            'facility_staff_id' => $fsB->id,
            'start_at' => $monday->copy()->addHours(14),
            'end_at' => $monday->copy()->addHours(16),
            'reason' => 'Training',
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/calendar');

        $response->assertOk();
        $events = $response->json('events');
        $this->assertCount(2, $events);
    }
}
