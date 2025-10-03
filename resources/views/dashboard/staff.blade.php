@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-slate-800">Fila do dia • {{ $client->name }}</h2>
        <p class="text-sm text-slate-500">Atualize status após cada contato e registre notas importantes.</p>
    </div>

    <div class="space-y-4">
        @forelse($jobs as $item)
            @php($job = $item['model'])
            <div class="rounded-lg bg-white p-5 shadow">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">{{ $job->cart->customer_name }} • R$ {{ number_format($job->cart->total, 2, ',', '.') }}</h3>
                        <p class="text-sm text-slate-500">{{ ucfirst(str_replace('_', ' ', $job->step)) }} • Agendado para {{ $job->scheduled_at->format('d/m H:i') }}</p>
                        <p class="text-xs text-slate-400">{{ $job->cart->customer_phone }} • <a href="{{ $item['resume_url'] }}" target="_blank" class="text-blue-600 hover:underline">Link de retomada</a></p>
                    </div>
                    <div class="flex gap-2 text-xs">
                        <form method="POST" action="{{ route('staff.jobs.reschedule', $job) }}">
                            @csrf
                            <button type="submit" class="rounded border border-slate-300 px-3 py-2 hover:bg-slate-50">Reenviar</button>
                        </form>
                        <form method="POST" action="{{ route('staff.jobs.contact', $job) }}">
                            @csrf
                            <button type="submit" class="rounded bg-emerald-600 px-3 py-2 font-semibold text-white hover:bg-emerald-500">Marcar contatado</button>
                        </form>
                    </div>
                </div>
                <form method="POST" action="{{ route('staff.jobs.notes', $job) }}" class="mt-4 text-sm">
                    @csrf
                    <label class="block text-slate-600">Adicionar nota</label>
                    <div class="mt-1 flex gap-2">
                        <textarea name="note" rows="2" class="w-full rounded border border-slate-300 px-3 py-2" placeholder="Resumo do contato"></textarea>
                        <button type="submit" class="rounded bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-700">Salvar</button>
                    </div>
                </form>
            </div>
        @empty
            <div class="rounded border border-dashed border-slate-300 p-8 text-center text-slate-400">
                Nenhum job pendente para hoje. Aproveite para revisar contatos anteriores.
            </div>
        @endforelse
    </div>
@endsection
