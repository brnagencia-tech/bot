<?php

namespace App\Http\Middleware;

use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $tenant = CurrentTenant::get();

        if (!$user || !$tenant) {
            abort(403);
        }

        $role = $user->tenants()->where('tenants.id', $tenant->id)->first()?->pivot?->role;
        if ($role && in_array($role, $roles, true)) {
            return $next($request);
        }

        abort(403);
    }
}

