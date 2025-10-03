<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class OpenAIClient
{
    public function chat(array $messages, ?string $model = null, float $temperature = 0.3): string
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            throw new \RuntimeException('OPENAI_API_KEY not configured');
        }

        $base = rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $model = $model ?: config('services.openai.model', 'gpt-4o-mini');

        $payload = [
            'model' => $model,
            'temperature' => $temperature,
            'messages' => $messages,
        ];

        $resp = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->post($base.'/chat/completions', $payload);

        if (!$resp->successful()) {
            throw new \RuntimeException('OpenAI error: '.$resp->status().' '.$resp->body());
        }

        $data = $resp->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }
}

