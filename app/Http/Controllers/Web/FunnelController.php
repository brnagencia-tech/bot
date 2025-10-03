<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FunnelController extends Controller
{
    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($client);

        $data = $request->validate([
            'after_1h' => ['nullable', 'boolean'],
            'after_2h' => ['nullable', 'boolean'],
            'after_24h' => ['nullable', 'boolean'],
            'after_48h' => ['nullable', 'boolean'],
            'text_after_1h' => ['nullable', 'string', 'max:500'],
            'text_after_2h' => ['nullable', 'string', 'max:500'],
            'text_after_24h' => ['nullable', 'string', 'max:500'],
            'text_after_48h' => ['nullable', 'string', 'max:500'],
        ]);

        $funnel = [];

        foreach (['after_1h', 'after_2h', 'after_24h', 'after_48h'] as $step) {
            $textKey = 'text_' . $step;
            $funnel[$step] = [
                'enabled' => (bool) ($data[$step] ?? false),
                'template' => 'cart_reminder_template_v1',
                'text' => $data[$textKey] ?? null,
            ];
        }

        $config = $client->config_json ?? [];
        $config['funnel'] = $funnel;
        $client->config_json = $config;
        $client->save();

        return back()->with('status', 'Configuração do funil atualizada.');
    }

    public function toggle(Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($client);

        $client->paused_at = $client->paused_at ? null : now();
        $client->save();

        return back()->with('status', $client->paused_at ? 'Campanha pausada.' : 'Campanha ativada.');
    }

    private function authorizeClientAccess(Client $client): void
    {
        $user = auth()->user();

        if ($user->role === UserRole::MASTER) {
            return;
        }

        if ($user->role === UserRole::ADMIN && $user->client_id === $client->id) {
            return;
        }

        abort(403, 'Acesso negado.');
    }
}
