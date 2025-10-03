<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;

class UserController
{
    public function profileForm()
    {
    Auth::requireLogin();
    \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
        $user = Auth::user();
        require __DIR__ . '/../Views/profile.php';
    }

    public function profile()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        if (!$name) $errors[] = 'Nome obrigatório.';
        if ($errors) {
            foreach ($errors as $e) echo '<p style="color:red">' . e($e) . '</p>';
            require __DIR__ . '/../Views/profile.php';
            return;
        }
        User::updateProfile($user->id, ['name' => $name]);
        $_SESSION['profile_success'] = true;
        redirect('/profile');
    }
}
