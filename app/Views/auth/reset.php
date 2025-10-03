<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <form method="post" class="bg-white p-8 rounded shadow w-full max-w-sm">
        <input type="hidden" name="csrf_token" value="<?=e(\App\Core\CSRF::token())?>">
        <h1 class="text-2xl font-bold mb-6">Definir Nova Senha</h1>
        <label class="block mb-4">Nova Senha
            <input type="password" name="password" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Salvar</button>
        <a href="/login" class="block text-center text-gray-500 mt-4 hover:underline">Voltar</a>
    </form>
</body>
</html>
