<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappAccount;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    public function index()
    {
        $tenant = CurrentTenant::get();
        $accounts = $tenant ? WhatsappAccount::where('tenant_id', $tenant->id)->get() : collect();
        return view('admin.whatsapp.index', compact('tenant', 'accounts'));
    }

    public function store(Request $request)
    {
        $tenant = CurrentTenant::get();
        abort_unless($tenant, 404);

        $data = $request->validate([
            'phone_id' => 'required|string',
            'waba_id' => 'required|string',
            'access_token' => 'required|string',
        ]);

        WhatsappAccount::updateOrCreate(
            ['tenant_id' => $tenant->id, 'phone_id' => $data['phone_id']],
            [
                'waba_id' => $data['waba_id'],
                'access_token' => $data['access_token'],
                'status' => 'active',
            ]
        );

        return redirect()->back()->with('status', 'Conta WhatsApp salva.');
    }
}

