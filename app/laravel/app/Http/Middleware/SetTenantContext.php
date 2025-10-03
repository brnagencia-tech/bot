<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->session()->get('tenant_id');

        if (!$tenantId && $request->user()) {
            $tenantId = $request->query('tenant_id');
            if (!$tenantId) {
                $tenantId = $request->user()->tenants()->value('tenants.id');
            }
            if ($tenantId) {
                $request->session()->put('tenant_id', $tenantId);
            }
        }

        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        CurrentTenant::set($tenant);

        return $next($request);
    }
}

