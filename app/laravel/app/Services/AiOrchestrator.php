<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Conversation;

class AiOrchestrator
{
    public function route(Conversation $conversation, string $preferredRole = null): array
    {
        // Placeholder for Python/FastAPI integration.
        // Return a dummy response format: ['message' => '...', 'actions' => [...]]
        $role = $preferredRole ?? 'SDR';
        $agent = Agent::where('tenant_id', $conversation->tenant_id)->where('role', $role)->first();

        $text = 'Olá! Sou seu agente virtual. Em breve responderei de forma inteligente.';
        if ($agent && $agent->role === 'SUPORTE') {
            $text = 'Obrigado pela mensagem! Estamos fora do expediente, registramos sua solicitação.';
        }

        return [
            'message' => $text,
            'actions' => [],
        ];
    }
}

