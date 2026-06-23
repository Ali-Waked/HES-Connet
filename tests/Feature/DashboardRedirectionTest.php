<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardRedirectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        $this->artisan('migrate', ['--force' => true]);
    }

    private function createRole(string $name): Role
    {
        return Role::create([
            'name' => ['en' => $name, 'ar' => ''],
            'slug' => $name,
            'uuid' => Str::uuid(),
        ]);
    }

    private function createUser(string $roleName): User
    {
        $role = $this->createRole($roleName);

        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        if (in_array($roleName, ['super_admin', 'facility_owner'])) {
            $user->systemRoles()->attach($role->id);
        } elseif ($roleName === 'doctor') {
            $staff = Staff::create(['user_id' => $user->id, 'status' => 'active']);
            $staff->facilityStaff()->create(['role_id' => $role->id, 'joined_at' => now()]);
        } elseif ($roleName === 'patient') {
            Patient::create(['user_id' => $user->id]);
        }

        return $user;
    }

    public function test_login_returns_dashboard_route_for_super_admin(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'user' => [
                    'uuid',
                    'email',
                    'dashboard_route',
                    'role',
                ],
            ])
            ->assertJson([
                'success' => true,
                'user' => [
                    'dashboard_route' => '/admin/dashboard',
                ],
            ]);
    }

    public function test_login_returns_dashboard_route_for_facility_owner(): void
    {
        $user = $this->createUser('facility_owner');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.dashboard_route', '/facility/dashboard');
    }

    public function test_login_returns_dashboard_route_for_doctor(): void
    {
        $user = $this->createUser('doctor');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.dashboard_route', '/doctor/dashboard');
    }

    public function test_login_returns_dashboard_route_for_patient(): void
    {
        $user = $this->createUser('patient');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.dashboard_route', '/patient/dashboard');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_profile_returns_dashboard_route(): void
    {
        $user = $this->createUser('doctor');

        $response = $this->actingAs($user, 'web')->getJson('/api/profile');

        $response->assertOk()
            ->assertJsonStructure([
                'user' => [
                    'uuid',
                    'email',
                    'dashboard_route',
                    'role',
                    'profile',
                    'city',
                ],
            ])
            ->assertJsonPath('user.dashboard_route', '/doctor/dashboard');
    }

    public function test_profile_requires_authentication(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertUnauthorized();
    }

    public function test_admin_middleware_allows_super_admin(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/admin/contact-messages');

        $response->assertOk();
    }

    public function test_admin_middleware_blocks_facility_owner(): void
    {
        $user = $this->createUser('facility_owner');

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/admin/contact-messages');

        $response->assertForbidden();
    }

    public function test_facility_middleware_allows_facility_owner(): void
    {
        $user = $this->createUser('facility_owner');

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/dashboard');

        $response->assertOk();
    }

    public function test_facility_middleware_blocks_doctor(): void
    {
        $user = $this->createUser('doctor');

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/facility/dashboard');

        $response->assertForbidden();
    }

    public function test_doctor_route_group_requires_auth(): void
    {
        $response = $this->getJson('/api/doctor/dashboard');

        $response->assertUnauthorized();
    }

    public function test_patient_route_group_requires_auth(): void
    {
        $response = $this->getJson('/api/patient/dashboard');

        $response->assertUnauthorized();
    }

    public function test_logout_destroys_session(): void
    {
        $user = $this->createUser('doctor');

        $this->actingAs($user, 'web');

        $response = $this->postJson('/api/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        $this->assertGuest('web');
    }
}
