@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-slate-800">{{ $client->name }}</h2>
            <p class="text-sm text-slate-500">Gestão de carrinhos e funil de recuperação</p>
        </div>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('admin.campaign.toggle', $client) }}">
                @csrf
                <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold {{ $client->paused_at ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $client->paused_at ? 'Ativar Campanha' : 'Pausar Campanha' }}
                </button>
            </form>
        </div>
    </div>

    <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-slate-500">Pendentes</p>
            <p class="mt-2 text-2xl font-semibold text-slate-800">{{ $metrics['carts']['pending'] }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-slate-500">Pagos</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ $metrics['carts']['paid'] }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-slate-500">Taxa de Recuperação</p>
            <p class="mt-2 text-2xl font-semibold text-blue-600">{{ number_format($metrics['carts']['recovery_rate_percent'], 2) }}%</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-slate-500">Jobs Hoje</p>
            <p class="mt-2 text-2xl font-semibold text-slate-800">{{ $metrics['jobs']['scheduled_today'] }}</p>
        </div>
    </section>

    <div class="grid gap-8 lg:grid-cols-3">
        <section class="lg:col-span-2 rounded-lg bg-white p-6 shadow">
            <header class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold">Carrinhos</h3>
                <form method="GET" class="flex gap-3 text-sm">
                    <input type="hidden" name="client_id" value="{{ request('client_id') }}">
                    <select name="status" class="rounded border border-slate-300 px-2 py-1">
                        <option value="">Status</option>
                        @foreach(['pending' => 'Pendente', 'paid' => 'Pago', 'abandoned' => 'Abandonado', 'cancelled' => 'Cancelado'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded border border-slate-300 px-2 py-1">
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded border border-slate-300 px-2 py-1">
                    <button type="submit" class="rounded bg-slate-800 px-3 py-1 text-white">Filtrar</button>
                </form>
            </header>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Cliente</th>
                            <th class="px-3 py-2 text-left">Total</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Criado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($carts as $cart)
                            <tr>
                                <td class="px-3 py-3">
                                    <div class="font-semibold">{{ $cart->customer_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $cart->customer_phone }}</div>
                                </td>
                                <td class="px-3 py-3">R$ {{ number_format($cart->total, 2, ',', '.') }}</td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-xs capitalize">{{ $cart->status }}</span>
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-500">{{ $cart->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-sm text-slate-500">Nenhum carrinho encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $carts->links() }}
            </div>
        </section>

        <section class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-lg font-semibold mb-3">Configuração do Funil</h3>
            <form method="POST" action="{{ route('admin.funnel.update', $client) }}" class="space-y-4 text-sm">
                @csrf
                @foreach(['after_1h' => '1 hora', 'after_2h' => '2 horas', 'after_24h' => '24 horas', 'after_48h' => '48 horas'] as $key => $label)
                    <div class="rounded border border-slate-200 p-3">
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="{{ $key }}" value="1" @checked(data_get($funnel, "$key.enabled", true))>
                            Habilitar lembrete {{ $label }}
                        </label>
                        <textarea name="text_{{ $key }}" rows="2" class="mt-2 w-full rounded border border-slate-200 px-2 py-2" placeholder="Texto opcional">{{ data_get($funnel, "$key.text") }}</textarea>
                    </div>
                @endforeach
                <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-500">Salvar Funil</button>
            </form>

            <h3 class="mt-8 text-lg font-semibold">Próximos Jobs</h3>
            <ul class="mt-3 space-y-2 text-xs text-slate-600">
                @foreach($jobs as $job)
                    <li class="flex justify-between rounded border border-slate-200 px-2 py-2">
                        <span>{{ ucfirst(str_replace('_', ' ', $job->step)) }} • {{ $job->cart->customer_name }}</span>
                        <span>{{ $job->scheduled_at->format('d/m H:i') }}</span>
                    </li>
                @endforeach
                @if($jobs->isEmpty())
                    <li class="rounded border border-dashed border-slate-200 px-2 py-4 text-center text-slate-400">Nenhum job agendado.</li>
                @endif
            </ul>
        </section>
    </div>
@endsection
