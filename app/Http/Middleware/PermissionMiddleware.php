<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()?->can($permission)) {
            abort(403, __('You do not have the required permission: :permission.', ['permission' => $permission]));
        }

        return $next($request);
    }
}
