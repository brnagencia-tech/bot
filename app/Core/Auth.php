<?php
namespace App\Core;

use App\Models\User;

class Auth
{
    public static function user()
    {
        if (!empty($_SESSION['user_id'])) {
            return User::findById($_SESSION['user_id']);
        }
        return null;
    }

    public static function check()
    {
        return !empty($_SESSION['user_id']);
    }

    public static function requireLogin()
    {
        if (!self::check()) {
            redirect('/login');
            exit;
        }
    }
}
