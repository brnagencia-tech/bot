@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Painel Admin</h1>
    @if(session('status'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 border rounded">
            <div class="text-gray-500">Tenant Atual</div>
            <div class="text-xl">{{ $tenant?->name ?? '—' }}</div>
        </div>
        <div class="p-4 border rounded">
            <div class="text-gray-500">Leads</div>
            <div class="text-xl">{{ $leadCount }}</div>
        </div>
        <div class="p-4 border rounded">
            <div class="text-gray-500">Conversas Abertas</div>
            <div class="text-xl">{{ $openConversations }}</div>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <a class="underline text-blue-600" href="{{ route('admin.whatsapp') }}">Conectar WhatsApp</a>
        <a class="underline text-blue-600" href="{{ route('leads.kanban') }}">Kanban de Leads</a>
    </div>
</div>
@endsection

