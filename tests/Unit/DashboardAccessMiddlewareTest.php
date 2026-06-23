<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Middleware\DashboardAccessMiddleware;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DashboardAccessMiddlewareTest extends TestCase
{
    private function mockUserWithRole(string $roleName): User
    {
        $role = $this->createMock(Role::class);
        $role->method('__get')->willReturnMap([
            ['name', ['en' => $roleName, 'ar' => '']],
        ]);

        $user = $this->createMock(User::class);
        $user->method('__get')->willReturnMap([
            ['role', $role],
        ]);

        return $user;
    }

    private function makeRequestWithUser(?User $user): Request
    {
        $request = $this->createMock(Request::class);
        $request->method('user')->willReturn($user);

        return $request;
    }

    private function invokeMiddleware(Request $request, string $dashboard): mixed
    {
        $middleware = new DashboardAccessMiddleware;
        $next = fn ($req) => 'next called';

        return $middleware->handle($request, $next, $dashboard);
    }

    public function test_admin_middleware_allows_super_admin(): void
    {
        $request = $this->makeRequestWithUser($this->mockUserWithRole('super_admin'));
        $this->assertSame('next called', $this->invokeMiddleware($request, 'admin'));
    }

    public function test_admin_middleware_blocks_facility_owner(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $request = $this->makeRequestWithUser($this->mockUserWithRole('facility_owner'));
        $this->invokeMiddleware($request, 'admin');
    }

    public function test_admin_middleware_blocks_doctor(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $request = $this->makeRequestWithUser($this->mockUserWithRole('doctor'));
        $this->invokeMiddleware($request, 'admin');
    }

    public function test_admin_middleware_blocks_patient(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $request = $this->makeRequestWithUser($this->mockUserWithRole('patient'));
        $this->invokeMiddleware($request, 'admin');
    }

    public function test_facility_middleware_allows_super_admin(): void
    {
        $request = $this->makeRequestWithUser($this->mockUserWithRole('super_admin'));
        $this->assertSame('next called', $this->invokeMiddleware($request, 'facility'));
    }

    public function test_facility_middleware_allows_facility_owner(): void
    {
        $request = $this->makeRequestWithUser($this->mockUserWithRole('facility_owner'));
        $this->assertSame('next called', $this->invokeMiddleware($request, 'facility'));
    }

    public function test_facility_middleware_blocks_doctor(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $request = $this->makeRequestWithUser($this->mockUserWithRole('doctor'));
        $this->invokeMiddleware($request, 'facility');
    }

    public function test_doctor_middleware_allows_super_admin(): void
    {
        $request = $this->makeRequestWithUser($this->mockUserWithRole('super_admin'));
        $this->assertSame('next called', $this->invokeMiddleware($request, 'doctor'));
    }

    public function test_doctor_middleware_allows_doctor(): void
    {
        $request = $this->makeRequestWithUser($this->mockUserWithRole('doctor'));
        $this->assertSame('next called', $this->invokeMiddleware($request, 'doctor'));
    }

    public function test_doctor_middleware_blocks_facility_owner(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $request = $this->makeRequestWithUser($this->mockUserWithRole('facility_owner'));
        $this->invokeMiddleware($request, 'doctor');
    }

    public function test_patient_middleware_allows_super_admin(): void
    {
        $request = $this->makeRequestWithUser($this->mockUserWithRole('super_admin'));
        $this->assertSame('next called', $this->invokeMiddleware($request, 'patient'));
    }

    public function test_patient_middleware_allows_patient(): void
    {
        $request = $this->makeRequestWithUser($this->mockUserWithRole('patient'));
        $this->assertSame('next called', $this->invokeMiddleware($request, 'patient'));
    }

    public function test_patient_middleware_blocks_doctor(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $request = $this->makeRequestWithUser($this->mockUserWithRole('doctor'));
        $this->invokeMiddleware($request, 'patient');
    }

    public function test_middleware_aborts_401_when_no_user(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);

        $request = $this->makeRequestWithUser(null);
        $this->invokeMiddleware($request, 'admin');
    }

    public function test_middleware_aborts_403_for_unknown_dashboard(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $request = $this->makeRequestWithUser($this->mockUserWithRole('super_admin'));
        $this->invokeMiddleware($request, 'unknown');
    }
}
