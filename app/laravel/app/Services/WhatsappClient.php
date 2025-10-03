<?php

namespace App\Services;

use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Http;

class WhatsappClient
{
    public function sendText(WhatsappAccount $account, string $to, string $text): array
    {
        $endpoint = sprintf('https://graph.facebook.com/v19.0/%s/messages', $account->phone_id);
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [ 'body' => $text ],
        ];

        $resp = Http::withToken($account->access_token)
            ->acceptJson()
            ->asJson()
            ->post($endpoint, $payload);

        if (!$resp->successful()) {
            throw new \RuntimeException('WA send error: '.$resp->status().' '.$resp->body());
        }

        return $resp->json();
    }
}

