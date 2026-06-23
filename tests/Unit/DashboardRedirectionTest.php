<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FacilityStaff;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class DashboardRedirectionTest extends TestCase
{
    private function mockUser(array $overrides = []): User
    {
        $defaults = [
            'hasSystemRole' => false,
            'getActiveFacilityStaff' => null,
            'patientExists' => false,
        ];

        $attrs = array_merge($defaults, $overrides);

        $user = $this->createPartialMock(User::class, ['hasSystemRole', 'getActiveFacilityStaff', 'patientProfile']);

        $user->method('hasSystemRole')->willReturnCallback(
            fn ($roles) => in_array($attrs['hasSystemRole'], (array) $roles, true)
        );

        $user->method('getActiveFacilityStaff')->willReturn($attrs['getActiveFacilityStaff']);

        $patientMock = $this->createMock(Patient::class);
        $patientMock->method('exists')->willReturn($attrs['patientExists']);
        $user->method('patientProfile')->willReturn($patientMock);

        return $user;
    }

    public function test_super_admin_gets_admin_dashboard_route(): void
    {
        $user = $this->mockUser(['hasSystemRole' => 'super_admin']);

        $this->assertSame('/admin/dashboard', $user->dashboard_route);
    }

    public function test_facility_owner_gets_facility_dashboard_route(): void
    {
        $user = $this->mockUser(['hasSystemRole' => 'facility_owner']);

        $this->assertSame('/facility/dashboard', $user->dashboard_route);
    }

    public function test_doctor_gets_staff_dashboard_route(): void
    {
        $role = new Role;
        $role->slug = 'doctor';

        $fs = $this->createMock(FacilityStaff::class);
        $fs->role = $role;

        $user = $this->mockUser([
            'hasSystemRole' => false,
            'getActiveFacilityStaff' => $fs,
        ]);

        $this->assertSame('/staff/dashboard', $user->dashboard_route);
    }

    public function test_patient_gets_patient_dashboard_route(): void
    {
        $user = $this->mockUser([
            'hasSystemRole' => false,
            'patientExists' => true,
        ]);

        $this->assertSame('/patient/dashboard', $user->dashboard_route);
    }

    public function test_no_role_returns_default_route(): void
    {
        $user = $this->mockUser([
            'hasSystemRole' => false,
        ]);

        $this->assertSame('/', $user->dashboard_route);
    }
}
