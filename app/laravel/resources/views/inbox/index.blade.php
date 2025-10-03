@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Inbox (WhatsApp)</h1>
    @if(session('status'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-3 mb-4">{{ session('status') }}</div>
    @endif

    @forelse($conversations as $c)
    <div class="border rounded mb-6">
        <div class="p-3 bg-gray-50 flex justify-between items-center">
            <div>
                <div class="font-semibold">Conversa #{{ $c->id }} — {{ $c->lead?->name ?? $c->lead?->phone ?? 'Lead' }}</div>
                <div class="text-xs text-gray-600">Status: {{ $c->status }} · Canal: {{ $c->channel }}</div>
            </div>
        </div>
        <div class="p-3 space-y-2">
            @foreach($c->messages as $m)
                <div class="p-2 border rounded {{ $m->direction === 'out' ? 'bg-blue-50' : 'bg-white' }}">
                    <div class="text-sm">{{ $m->body }}</div>
                    <div class="text-[10px] text-gray-500">{{ $m->created_at }} · {{ strtoupper($m->direction) }}</div>
                </div>
            @endforeach
        </div>
        <div class="p-3 border-t bg-gray-50">
            <form method="POST" action="{{ url('/conversations/'.$c->id.'/reply') }}" class="flex gap-2">
                @csrf
                <input name="text" class="border p-2 flex-1" placeholder="Responder..." required />
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Enviar</button>
            </form>
        </div>
    </div>
    @empty
        <div class="text-gray-600">Nenhuma conversa.</div>
    @endforelse
</div>
@endsection

