<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Stage;

class StageController
{
    public function editForm($id = null)
    {
    Auth::requireLogin();
    \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
        $user = Auth::user();
        $id = $id ?? ($_GET['id'] ?? null);
        $stage = \App\Models\Stage::findByIdUser($id, $user->id);
        if (!$stage) redirect('/dashboard');
        require __DIR__ . '/../Views/stages/edit.php';
    }

    public function edit($id = null)
    {
    Auth::requireLogin();
    \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
        $user = Auth::user();
        $id = $id ?? ($_POST['id'] ?? null);
        $stage = \App\Models\Stage::findByIdUser($id, $user->id);
        if (!$stage) redirect('/dashboard');
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        if (!$name) $errors[] = 'Nome obrigatório.';
        if ($errors) {
            foreach ($errors as $e) echo '<p style="color:red">' . e($e) . '</p>';
            require __DIR__ . '/../Views/stages/edit.php';
            return;
        }
        \App\Models\Stage::update($id, $user->id, $name);
        redirect('/dashboard');
    }

    public function delete($id = null)
    {
    Auth::requireLogin();
    \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
        $user = Auth::user();
        $id = $id ?? ($_POST['id'] ?? $_GET['id'] ?? null);
        // Só permite excluir se houver mais de 1 coluna
        $stages = \App\Models\Stage::allByUser($user->id);
        if (count($stages) <= 1) {
            echo '<p style="color:red">É necessário ao menos uma coluna.</p>';
            redirect('/dashboard');
        }
        \App\Models\Stage::delete($id, $user->id);
        redirect('/dashboard');
    }
    public function createForm()
    {
        Auth::requireLogin();
        require __DIR__ . '/../Views/stages/create.php';
    }

    public function create()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        if (!$name) $errors[] = 'Nome obrigatório.';
        if ($errors) {
            foreach ($errors as $e) echo '<p style="color:red">' . e($e) . '</p>';
            require __DIR__ . '/../Views/stages/create.php';
            return;
        }
        // Posição: última
        $position = count(Stage::allByUser($user->id));
        Stage::create($user->id, $name, $position);
        redirect('/dashboard');
    }

    // Métodos para editar, excluir, reordenar podem ser adicionados depois
}
