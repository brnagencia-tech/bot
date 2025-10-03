<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Stage
{
    public static function findByIdUser($id, $user_id)
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM stages WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $user_id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch();
    }

    public static function update($id, $user_id, $name)
    {
        $stmt = DB::getInstance()->prepare('UPDATE stages SET name = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        return $stmt->execute([$name, $id, $user_id]);
    }

    public static function delete($id, $user_id)
    {
        $stmt = DB::getInstance()->prepare('DELETE FROM stages WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $user_id]);
    }
//
    public $id, $user_id, $name, $position, $created_at, $updated_at;

    public static function allByUser($user_id)
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM stages WHERE user_id = ? ORDER BY position ASC');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function create($user_id, $name, $position)
    {
        $stmt = DB::getInstance()->prepare('INSERT INTO stages (user_id, name, position, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$user_id, $name, $position]);
        return DB::getInstance()->lastInsertId();
    }

    // Métodos para update, delete, reorder, etc. podem ser adicionados conforme necessário
}
