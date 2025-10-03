<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Tenant;
use App\Support\CurrentTenant;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = CurrentTenant::get();
        $tenantCount = Tenant::count();
        $leadCount = $tenant ? Lead::where('tenant_id', $tenant->id)->count() : 0;
        $openConversations = $tenant ? Conversation::where('tenant_id', $tenant->id)->where('status', 'open')->count() : 0;

        return view('admin.dashboard', compact('tenant', 'tenantCount', 'leadCount', 'openConversations'));
    }
}

