<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Support\CurrentTenant;

class InboxController extends Controller
{
    public function index()
    {
        $tenant = CurrentTenant::get();
        abort_unless($tenant, 404);

        $conversations = Conversation::with(['lead', 'messages' => function ($q) {
            $q->orderBy('id');
        }])->where('tenant_id', $tenant->id)
          ->orderByDesc('id')
          ->limit(50)
          ->get();

        return view('inbox.index', compact('conversations'));
    }
}

