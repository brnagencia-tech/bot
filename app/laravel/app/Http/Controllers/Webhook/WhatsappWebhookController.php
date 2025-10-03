<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Message;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token && $token === config('services.whatsapp.verify_token')) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function receive(Request $request)
    {
        $payload = $request->all();
        Log::info('WA webhook', $payload);

        $entry = $payload['entry'][0] ?? null;
        $changes = $entry['changes'][0] ?? null;
        $value = $changes['value'] ?? null;
        $messages = $value['messages'] ?? [];
        $metadata = $value['metadata'] ?? [];
        $phoneId = $metadata['phone_number_id'] ?? null;

        if (!$phoneId) {
            return response()->json(['ok' => true]);
        }

        $account = WhatsappAccount::where('phone_id', $phoneId)->first();
        if (!$account) {
            return response()->json(['ok' => true]);
        }

        foreach ($messages as $msg) {
            $from = $msg['from'] ?? null; // customer phone
            $waMessageId = $msg['id'] ?? null;
            $text = $msg['text']['body'] ?? null;

            if (!$from || !$waMessageId) continue;

            $lead = Lead::firstOrCreate(
                ['tenant_id' => $account->tenant_id, 'phone' => $from],
                ['name' => null, 'stage_id' => null]
            );

            $conversation = Conversation::firstOrCreate(
                ['tenant_id' => $account->tenant_id, 'lead_id' => $lead->id, 'status' => 'open'],
                ['channel' => 'whatsapp']
            );

            Message::firstOrCreate(
                ['conversation_id' => $conversation->id, 'wa_message_id' => $waMessageId],
                [
                    'direction' => 'in',
                    'body' => $text,
                    'received_at' => now(),
                    'status' => 'received',
                ]
            );
        }

        return response()->json(['ok' => true]);
    }
}
