@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">WhatsApp</h1>
    @if(session('status'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-3 mb-4">{{ session('status') }}</div>
    @endif

    @if(!$tenant)
        <div class="bg-yellow-100 p-3">Selecione um tenant.</div>
    @else
    <form method="POST" action="{{ route('admin.whatsapp.store') }}" class="border rounded p-4 max-w-xl">
        @csrf
        <div class="space-y-3">
            <input class="border p-2 w-full" name="phone_id" placeholder="Phone Number ID" required />
            <input class="border p-2 w-full" name="waba_id" placeholder="WABA ID" required />
            <textarea class="border p-2 w-full" name="access_token" placeholder="Access Token" required></textarea>
        </div>
        <button class="mt-3 bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
    </form>

    <h2 class="text-xl font-semibold mt-6 mb-2">Contas Conectadas</h2>
    <ul class="list-disc ml-6">
        @forelse($accounts as $acc)
            <li>{{ $acc->phone_id }} ({{ $acc->status }})</li>
        @empty
            <li>Nenhuma conta.</li>
        @endforelse
    </ul>
    @endif
</div>
@endsection

