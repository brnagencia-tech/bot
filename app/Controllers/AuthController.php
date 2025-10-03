<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Tenant;
use DomainException;
use App\Services\AuthService;

class AuthController
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function loginForm()
    {
        require __DIR__ . '/../Views/auth/login.php';
    }
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
            redirect('/login');
        }
        \App\Core\RateLimiter::check('login', 5, 5);
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            $result = $this->service->login($email, $password);
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['api_token'] = $result['token'];
            $_SESSION['tenant_id'] = $result['tenants'][0]['tenant_id'] ?? null;
            $_SESSION['login_success'] = true;
            redirect('/dashboard');
        } catch (DomainException $e) {
            $_SESSION['login_error'] = 'Email ou senha inválidos.';
            redirect('/login');
        }
    }
    public function registerForm()
    {
        require __DIR__ . '/../Views/auth/register.php';
    }
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            \App\Core\CSRF::check($_POST['csrf_token'] ?? '');
            redirect('/register');
        }
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];
        if (!$name) $errors[] = 'Nome obrigatório.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
        if (strlen($password) < 6) $errors[] = 'Senha deve ter ao menos 6 caracteres.';
        if (\App\Models\User::findByEmail($email)) $errors[] = 'Email já cadastrado.';
        if ($errors) {
            $_SESSION['register_error'] = implode('<br>', array_map('e', $errors));
            $_SESSION['register_name'] = $name;
            $_SESSION['register_email'] = $email;
            redirect('/register');
        }
        $tenant = Tenant::findByName('Tenant Demo');
        if (!$tenant) {
            $tenant = Tenant::create('Tenant Demo');
        }

        $this->service->register([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'tenant_id' => $tenant->id,
        ]);
        $_SESSION['register_success'] = true;
        redirect('/login');
    }
    public function logout()
    {
        $user = Auth::user();
        if ($user) {
            $this->service->logout($user);
        }
        session_unset();
        session_destroy();
        redirect('/login');
    }
}
