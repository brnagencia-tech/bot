<?php
namespace App\Core;

class CSRF
{
    public static function token()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function check($token)
    {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            die('CSRF token inválido.');
        }
        // Renova token após uso
        unset($_SESSION['csrf_token']);
    }
}
