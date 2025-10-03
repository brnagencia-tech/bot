<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Lead</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <form method="post" class="bg-white p-8 rounded shadow w-full max-w-md">
        <input type="hidden" name="csrf_token" value="<?=e(\App\Core\CSRF::token())?>">
        <h1 class="text-2xl font-bold mb-6">Novo Lead</h1>
        <label class="block mb-2">Nome
            <input type="text" name="name" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-2">Email
            <input type="email" name="email" class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-2">Telefone
            <input type="text" name="phone" class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-2">Valor Potencial (R$)
            <input type="number" step="0.01" name="value" class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-2">Origem
            <input type="text" name="origin" class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-4">Notas
            <textarea name="notes" class="mt-1 w-full border rounded px-3 py-2"></textarea>
        </label>
        <label class="block mb-4">Coluna
            <select name="stage_id" required class="mt-1 w-full border rounded px-3 py-2">
                <option value="">Selecione...</option>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?=e($stage->id)?>"><?=e($stage->name)?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Salvar</button>
        <a href="/dashboard" class="block text-center text-gray-500 mt-4 hover:underline">Cancelar</a>
    </form>
</body>
</html>
