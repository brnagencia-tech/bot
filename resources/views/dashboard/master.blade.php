@extends('layouts.app')

@section('content')
    <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-slate-500">Carrinhos Pendentes</p>
            <p class="mt-2 text-2xl font-semibold text-slate-800">{{ $metrics['carts']['pending'] }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-slate-500">Carrinhos Pagos</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ $metrics['carts']['paid'] }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-slate-500">Taxa de Recuperação</p>
            <p class="mt-2 text-2xl font-semibold text-blue-600">{{ number_format($metrics['recovery_rate_percent'], 2) }}%</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-slate-500">Mensagens Enviadas</p>
            <p class="mt-2 text-2xl font-semibold text-slate-800">{{ $metrics['messages']['sent'] }}</p>
        </div>
    </section>

    <section class="grid gap-8 lg:grid-cols-3">
        <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Clientes</h2>
                <form method="POST" action="{{ route('master.clients.store') }}" class="flex items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Nome</label>
                        <input type="text" name="name" required class="mt-1 w-48 rounded-md border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">WhatsApp</label>
                        <input type="text" name="whatsapp_phone" class="mt-1 w-40 rounded-md border border-slate-300 px-3 py-2 text-sm" placeholder="+5511999999999">
                    </div>
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Novo Cliente</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Cliente</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Pendentes</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Pagos</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Jobs vencendo</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($clients as $client)
                            <tr>
                                <td class="px-3 py-3">
                                    <div class="font-semibold text-slate-800">{{ $client->name }}</div>
                                    <div class="text-xs text-slate-500">ID {{ $client->id }} • {{ $client->is_active ? 'Ativo' : 'Inativo' }}</div>
                                </td>
                                <td class="px-3 py-3">{{ $client->carts_pending_count }}</td>
                                <td class="px-3 py-3 text-emerald-600">{{ $client->carts_paid_count }}</td>
                                <td class="px-3 py-3">{{ $client->jobs_due_count }}</td>
                                <td class="px-3 py-3 text-right">
                                    <div class="flex justify-end gap-2 text-xs">
                                        <a href="{{ route('admin.dashboard', ['client_id' => $client->id]) }}" class="rounded border border-slate-200 px-3 py-1 hover:bg-slate-50">Ver Admin</a>
                                        <form method="POST" action="{{ route('master.clients.toggle', $client) }}">
                                            @csrf
                                            <button type="submit" class="rounded border border-slate-200 px-3 py-1 hover:bg-slate-50">
                                                {{ $client->paused_at ? 'Ativar' : 'Pausar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr class="bg-slate-50/50">
                                <td colspan="5" class="px-4 py-4">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <h3 class="text-sm font-semibold mb-2">Credenciais Meta</h3>
                                            <form method="POST" action="{{ route('master.clients.credentials', $client) }}" class="grid gap-2 text-xs">
                                                @csrf
                                                <label class="flex flex-col">
                                                    <span class="text-slate-500">Phone Number ID</span>
                                                    <input type="text" name="meta_phone_id" value="{{ $client->meta_phone_id }}" class="rounded border border-slate-200 px-3 py-2">
                                                </label>
                                                <label class="flex flex-col">
                                                    <span class="text-slate-500">Access Token</span>
                                                    <input type="text" name="meta_access_token" class="rounded border border-slate-200 px-3 py-2" placeholder="Cole aqui o token">
                                                </label>
                                                <label class="flex flex-col">
                                                    <span class="text-slate-500">WhatsApp Principal</span>
                                                    <input type="text" name="whatsapp_phone" value="{{ $client->whatsapp_phone }}" class="rounded border border-slate-200 px-3 py-2">
                                                </label>
                                                <button type="submit" class="mt-2 w-max rounded bg-blue-600 px-3 py-2 font-semibold text-white hover:bg-blue-500">Salvar Credenciais</button>
                                            </form>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold mb-2">Usuários</h3>
                                            <ul class="space-y-1 text-xs text-slate-600 mb-3">
                                                @forelse($usersByClient[$client->id] ?? [] as $user)
                                                    <li>{{ $user->name }} • {{ $user->email }} • {{ ucfirst($user->role->value) }}</li>
                                                @empty
                                                    <li>Nenhum usuário vinculado.</li>
                                                @endforelse
                                            </ul>
                                            <form method="POST" action="{{ route('master.clients.users.store', $client) }}" class="grid gap-2 text-xs">
                                                @csrf
                                                <div class="flex gap-2">
                                                    <input type="text" name="name" placeholder="Nome" class="w-1/2 rounded border border-slate-200 px-2 py-2" required>
                                                    <input type="email" name="email" placeholder="email@dominio" class="w-1/2 rounded border border-slate-200 px-2 py-2" required>
                                                </div>
                                                <div class="flex gap-2">
                                                    <select name="role" class="w-1/2 rounded border border-slate-200 px-2 py-2">
                                                        <option value="admin">Admin</option>
                                                        <option value="staff">Funcionário</option>
                                                    </select>
                                                    <input type="password" name="password" placeholder="Senha temporária" class="w-1/2 rounded border border-slate-200 px-2 py-2" required>
                                                </div>
                                                <button type="submit" class="w-max rounded bg-slate-800 px-3 py-2 font-semibold text-white hover:bg-slate-700">Adicionar Usuário</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-lg font-semibold mb-4">Resumo Geral</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Mensagens Falhas</dt>
                    <dd>{{ $metrics['messages']['failed'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Conversões após envio</dt>
                    <dd>{{ $metrics['conversions_after_message'] }}</dd>
                </div>
            </dl>
        </div>
    </section>
@endsection
