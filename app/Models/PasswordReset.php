<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class PasswordReset
{
    public static function create($user_id, $token)
    {
        $stmt = DB::getInstance()->prepare('INSERT INTO password_resets (user_id, token, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$user_id, $token]);
    }

    public static function findValid($token)
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM password_resets WHERE token = ? AND used = 0 AND created_at >= (NOW() - INTERVAL 60 MINUTE) LIMIT 1');
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public static function invalidate($token)
    {
        $stmt = DB::getInstance()->prepare('UPDATE password_resets SET used = 1 WHERE token = ?');
        $stmt->execute([$token]);
    }
}
