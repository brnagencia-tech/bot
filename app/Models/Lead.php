<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Lead
    public static function searchByStage($user_id, $stage_id, $q)
    {
        $q = '%' . $q . '%';
        $stmt = DB::getInstance()->prepare('SELECT * FROM leads WHERE user_id = ? AND stage_id = ? AND (name LIKE ? OR phone LIKE ? OR origin LIKE ?) ORDER BY position ASC');
        $stmt->execute([$user_id, $stage_id, $q, $q, $q]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }
    public static function updatePosition($id, $user_id, $stage_id, $position)
    {
        $stmt = DB::getInstance()->prepare('UPDATE leads SET stage_id=?, position=?, updated_at=NOW() WHERE id=? AND user_id=?');
        return $stmt->execute([$stage_id, $position, $id, $user_id]);
    }
    public static function findByIdUser($id, $user_id)
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM leads WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $user_id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch();
    }

    public static function update($id, $user_id, $data)
    {
        $stmt = DB::getInstance()->prepare('UPDATE leads SET name=?, email=?, phone=?, value=?, origin=?, notes=?, stage_id=?, updated_at=NOW() WHERE id=? AND user_id=?');
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['value'],
            $data['origin'],
            $data['notes'],
            $data['stage_id'],
            $id,
            $user_id
        ]);
    }

    public static function delete($id, $user_id)
    {
        $stmt = DB::getInstance()->prepare('DELETE FROM leads WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $user_id]);
    }
{
    public $id, $user_id, $stage_id, $name, $email, $phone, $value, $origin, $notes, $position, $created_at, $updated_at;

    public static function allByUser($user_id)
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM leads WHERE user_id = ? ORDER BY position ASC');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function allByStage($user_id, $stage_id)
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM leads WHERE user_id = ? AND stage_id = ? ORDER BY position ASC');
        $stmt->execute([$user_id, $stage_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function create($data)
    {
        $stmt = DB::getInstance()->prepare('INSERT INTO leads (user_id, stage_id, name, email, phone, value, origin, notes, position, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $data['user_id'],
            $data['stage_id'],
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['value'],
            $data['origin'],
            $data['notes'],
            $data['position'] ?? 0
        ]);
        return DB::getInstance()->lastInsertId();
    }

    public static function findByIdUser($id, $user_id)
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM leads WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $user_id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch();
    }

    public static function update($id, $user_id, $data)
    {
        $stmt = DB::getInstance()->prepare('UPDATE leads SET name=?, email=?, phone=?, value=?, origin=?, notes=?, stage_id=?, updated_at=NOW() WHERE id=? AND user_id=?');
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['value'],
            $data['origin'],
            $data['notes'],
            $data['stage_id'],
            $id,
            $user_id
        ]);
    }

    public static function delete($id, $user_id)
    {
        $stmt = DB::getInstance()->prepare('DELETE FROM leads WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $user_id]);
    }

    // Métodos para update, delete, mover, etc. podem ser adicionados conforme necessário
}
