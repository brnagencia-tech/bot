@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md bg-white shadow rounded-lg p-8">
        <h2 class="text-xl font-semibold mb-6 text-center">Acessar Plataforma</h2>
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Senha</label>
                <input type="password" name="password" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <button type="submit" class="w-full rounded-md bg-blue-600 py-2 text-white font-semibold hover:bg-blue-500">Entrar</button>
        </form>
    </div>
@endsection
