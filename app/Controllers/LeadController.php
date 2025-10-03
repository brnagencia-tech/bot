<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Lead;
use App\Models\Stage;

class LeadController
    public function editForm($id = null)
    {
    Auth::requireLogin();
    \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
        $user = Auth::user();
        $id = $id ?? ($_GET['id'] ?? null);
        $lead = \App\Models\Lead::findByIdUser($id, $user->id);
        if (!$lead) redirect('/dashboard');
        $stages = \App\Models\Stage::allByUser($user->id);
        require __DIR__ . '/../Views/leads/edit.php';
    }

    public function edit($id = null)
    {
    Auth::requireLogin();
    \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
        $user = Auth::user();
        $id = $id ?? ($_POST['id'] ?? null);
        $lead = \App\Models\Lead::findByIdUser($id, $user->id);
        if (!$lead) redirect('/dashboard');
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'value' => $_POST['value'] ?? null,
            'origin' => trim($_POST['origin'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'stage_id' => $_POST['stage_id'] ?? $lead->stage_id
        ];
        $errors = [];
        if (!$data['name']) $errors[] = 'Nome obrigatório.';
        if (!$data['stage_id']) $errors[] = 'Coluna obrigatória.';
        if ($errors) {
            $stages = \App\Models\Stage::allByUser($user->id);
            foreach ($errors as $e) echo '<p style="color:red">' . e($e) . '</p>';
            require __DIR__ . '/../Views/leads/edit.php';
            return;
        }
        \App\Models\Lead::update($id, $user->id, $data);
        redirect('/dashboard');
    }

    public function delete($id = null)
    {
    Auth::requireLogin();
    \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
        $user = Auth::user();
        $id = $id ?? ($_POST['id'] ?? $_GET['id'] ?? null);
        \App\Models\Lead::delete($id, $user->id);
        redirect('/dashboard');
    }
{
    public function createForm()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $stages = Stage::allByUser($user->id);
        require __DIR__ . '/../Views/leads/create.php';
    }

    public function create()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $data = [
            'user_id' => $user->id,
            'stage_id' => $_POST['stage_id'] ?? null,
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'value' => $_POST['value'] ?? null,
            'origin' => trim($_POST['origin'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'position' => 0
        ];
        // Validação simples
        $errors = [];
        if (!$data['name']) $errors[] = 'Nome obrigatório.';
        if (!$data['stage_id']) $errors[] = 'Coluna obrigatória.';
        if ($errors) {
            $stages = Stage::allByUser($user->id);
            foreach ($errors as $e) echo '<p style="color:red">' . e($e) . '</p>';
            require __DIR__ . '/../Views/leads/create.php';
            return;
        }
        Lead::create($data);
        redirect('/dashboard');
    }

    public function editForm($id = null)
    {
        Auth::requireLogin();
        $user = Auth::user();
        $id = $id ?? ($_GET['id'] ?? null);
        $lead = \App\Models\Lead::findByIdUser($id, $user->id);
        if (!$lead) redirect('/dashboard');
        $stages = \App\Models\Stage::allByUser($user->id);
        require __DIR__ . '/../Views/leads/edit.php';
    }

    public function edit($id = null)
    {
        Auth::requireLogin();
        $user = Auth::user();
        $id = $id ?? ($_POST['id'] ?? null);
        $lead = \App\Models\Lead::findByIdUser($id, $user->id);
        if (!$lead) redirect('/dashboard');
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'value' => $_POST['value'] ?? null,
            'origin' => trim($_POST['origin'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'stage_id' => $_POST['stage_id'] ?? $lead->stage_id
        ];
        $errors = [];
        if (!$data['name']) $errors[] = 'Nome obrigatório.';
        if (!$data['stage_id']) $errors[] = 'Coluna obrigatória.';
        if ($errors) {
            $stages = \App\Models\Stage::allByUser($user->id);
            foreach ($errors as $e) echo '<p style="color:red">' . e($e) . '</p>';
            require __DIR__ . '/../Views/leads/edit.php';
            return;
        }
        \App\Models\Lead::update($id, $user->id, $data);
        redirect('/dashboard');
    }

    public function delete($id = null)
    {
        Auth::requireLogin();
        $user = Auth::user();
        $id = $id ?? ($_POST['id'] ?? $_GET['id'] ?? null);
        \App\Models\Lead::delete($id, $user->id);
        redirect('/dashboard');
    }

    // Métodos para editar, excluir, etc. podem ser adicionados conforme necessário
}
