<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - CRM SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <?php if (!empty($_SESSION['register_error'])): ?>
        <div class="bg-red-100 text-red-800 px-3 py-2 rounded mb-4"><?=$_SESSION['register_error']; unset($_SESSION['register_error']);?></div>
    <?php endif; ?>
    <form method="post" class="bg-white p-8 rounded shadow w-full max-w-sm">
        <input type="hidden" name="csrf_token" value="<?=e(\App\Core\CSRF::token())?>">
        <h1 class="text-2xl font-bold mb-6">Criar Conta</h1>
        <label class="block mb-2">Nome
            <input type="text" name="name" value="<?=e($_SESSION['register_name'] ?? '')?>" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-2">Email
            <input type="email" name="email" value="<?=e($_SESSION['register_email'] ?? '')?>" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-4">Senha
            <input type="password" name="password" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Criar Conta</button>
        <div class="flex justify-between mt-4 text-sm">
            <a href="/login" class="text-blue-600 hover:underline">Entrar</a>
        </div>
    </form>
</body>
</html>
