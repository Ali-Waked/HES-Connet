<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if ($user->hasSystemRole('super_admin')) {
            return $next($request);
        }

        $allowed = match ($role) {
            'admin' => false,
            'facility' => $user->staff && $user->staff->facilityStaff()->whereNull('ended_at')->exists(),
            'doctor' => $user->staff && $user->staff->facilityStaff()
                ->whereNull('ended_at')
                ->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))
                ->exists(),
            'patient' => $user->patientProfile()->exists(),
            default => false,
        };

        if (!$allowed) {
            abort(403, __('You do not have access to this dashboard.'));
        }

        return $next($request);
    }
}
