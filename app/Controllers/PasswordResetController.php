<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;
use App\Models\PasswordReset;
use App\Core\RateLimiter;

class PasswordResetController
{
    public function requestForm()
    {
        require __DIR__ . '/../Views/auth/reset_request.php';
    }

    public function request()
    {
    RateLimiter::check('reset', 5, 5);
    \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $user = User::findByEmail($email);
        if (!$user) {
            echo '<p style="color:red">Se o email existir, um link será enviado.</p>';
            require __DIR__ . '/../Views/auth/reset_request.php';
            return;
        }
        $token = bin2hex(random_bytes(32));
        PasswordReset::create($user->id, $token);
        // Em dev, salva o link em arquivo
        $link = env('APP_URL', 'http://localhost') . "/reset/{$token}";
        file_put_contents(__DIR__ . '/../../reset_links.log', $link."\n", FILE_APPEND);
        echo '<p style="color:green">Se o email existir, um link será enviado.</p>';
        require __DIR__ . '/../Views/auth/reset_request.php';
    }

    public function form($token)
    {
    \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
    $reset = PasswordReset::findValid($token);
        if (!$reset) die('Token inválido ou expirado.');
        require __DIR__ . '/../Views/auth/reset.php';
    }

    public function update($token)
    {
        $reset = PasswordReset::findValid($token);
        if (!$reset) die('Token inválido ou expirado.');
        $password = $_POST['password'] ?? '';
        if (strlen($password) < 6) {
            echo '<p style="color:red">Senha deve ter ao menos 6 caracteres.</p>';
            require __DIR__ . '/../Views/auth/reset.php';
            return;
        }
        User::updatePassword($reset->user_id, $password);
        PasswordReset::invalidate($token);
        echo '<p style="color:green">Senha redefinida com sucesso. <a href="/login">Entrar</a></p>';
    }
}
