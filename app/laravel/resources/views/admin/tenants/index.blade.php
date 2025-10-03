@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Tenants</h1>
    @if(session('status'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-3 mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('master.tenants.store') }}" class="mb-6 border rounded p-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input class="border p-2" name="name" placeholder="Nome" required />
            <input class="border p-2" name="slug" placeholder="Slug (opcional)" />
            <input class="border p-2" name="domain" placeholder="Domínio (opcional)" />
        </div>
        <button class="mt-3 bg-blue-600 text-white px-4 py-2 rounded">Criar</button>
    </form>

    <table class="w-full border">
        <thead>
            <tr class="bg-gray-50">
                <th class="p-2 text-left">ID</th>
                <th class="p-2 text-left">Nome</th>
                <th class="p-2 text-left">Slug</th>
                <th class="p-2 text-left">Domínio</th>
                <th class="p-2 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tenants as $t)
            <tr>
                <td class="p-2 border-t">{{ $t->id }}</td>
                <td class="p-2 border-t">{{ $t->name }}</td>
                <td class="p-2 border-t">{{ $t->slug }}</td>
                <td class="p-2 border-t">{{ $t->domain }}</td>
                <td class="p-2 border-t">{{ $t->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

