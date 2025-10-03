<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\MetaCredentialsRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeMaster();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'whatsapp_phone' => ['nullable', 'string'],
        ]);

        $client = Client::create($data);

        return redirect()->route('master.dashboard')->with('status', "Cliente {$client->name} criado com sucesso.");
    }

    public function updateCredentials(MetaCredentialsRequest $request, Client $client): RedirectResponse
    {
        $this->authorizeMaster();

        $data = $request->validated();

        $client->fill([
            'meta_phone_id' => $data['meta_phone_id'],
            'meta_access_token' => $data['meta_access_token'],
            'whatsapp_phone' => $data['whatsapp_phone'] ?? $client->whatsapp_phone,
        ]);

        if (! empty($data['config'])) {
            $client->config_json = array_merge($client->config_json ?? [], $data['config']);
        }

        $client->save();

        return back()->with('status', 'Credenciais Meta atualizadas.');
    }

    public function storeUser(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeMaster();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:admin,staff'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'client_id' => $client->id,
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'role' => $data['role'] === 'admin' ? UserRole::ADMIN : UserRole::STAFF,
            'password_hash' => $data['password'],
        ]);

        return back()->with('status', 'Usuário criado com sucesso.');
    }

    public function toggleCampaign(Client $client): RedirectResponse
    {
        $this->authorizeMaster();

        $client->paused_at = $client->paused_at ? null : now();
        $client->save();

        return back()->with('status', $client->paused_at ? 'Campanha pausada.' : 'Campanha reativada.');
    }

    private function authorizeMaster(): void
    {
        abort_unless(auth()->user()?->role === UserRole::MASTER, 403);
    }
}
