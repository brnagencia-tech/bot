<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\WhatsappAccount;
use App\Services\WhatsappClient;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;

class MessageSendController extends Controller
{
    public function reply(Request $request, Conversation $conversation, WhatsappClient $client)
    {
        $tenant = CurrentTenant::get();
        abort_unless($tenant && $conversation->tenant_id === $tenant->id, 403);

        $data = $request->validate([
            'text' => 'required|string|max:4096',
        ]);

        $lead = $conversation->lead;
        abort_unless($lead && $lead->phone, 400, 'Lead sem telefone.');

        $account = WhatsappAccount::where('tenant_id', $tenant->id)->first();
        abort_unless($account, 400, 'Tenant sem conta WhatsApp.');

        $resp = $client->sendText($account, $lead->phone, $data['text']);
        $waId = $resp['messages'][0]['id'] ?? null;

        Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'out',
            'body' => $data['text'],
            'wa_message_id' => $waId,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('status', 'Mensagem enviada.');
    }
}

