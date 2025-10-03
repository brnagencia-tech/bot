<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Conversation;
use App\Services\Ai\OpenAIClient;

class AiOrchestrator
{
    public function route(Conversation $conversation, string $preferredRole = null): array
    {
        $role = $preferredRole ?? 'SDR';
        $agent = Agent::where('tenant_id', $conversation->tenant_id)->where('role', $role)->first();

        $apiKey = config('services.openai.api_key');

        // If OpenAI not configured, fallback to a simple canned reply
        if (!$apiKey) {
            $text = $agent && $agent->role === 'SUPORTE'
                ? 'Obrigado pela mensagem! Estamos fora do expediente, registramos sua solicitação.'
                : 'Olá! Obrigado por nos chamar. Em breve retornaremos.';

            return ['message' => $text, 'actions' => []];
        }

        // Build conversation into ChatML
        $messages = [];
        $system = $agent?->prompt ?: 'Você é um agente de atendimento. Responda de forma útil e breve.';
        $messages[] = ['role' => 'system', 'content' => $system];

        $history = $conversation->messages()->orderByDesc('id')->limit(15)->get()->reverse();
        foreach ($history as $m) {
            $messages[] = [
                'role' => $m->direction === 'out' ? 'assistant' : 'user',
                'content' => $m->body ?? '',
            ];
        }

        $client = app(OpenAIClient::class);
        $reply = $client->chat($messages, config('services.openai.model'), (float)($agent->temperature ?? 0.3));

        return [
            'message' => trim($reply) ?: 'Certo, obrigado! Em breve retorno com mais detalhes.',
            'actions' => [],
        ];
    }
}
