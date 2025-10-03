<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Role
{
    public int $id;
    public string $name;
    public string $created_at;
    public ?string $updated_at;

    public static function findByName(string $name): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM roles WHERE name = ? LIMIT 1');
        $stmt->execute([$name]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $role = $stmt->fetch();
        return $role ?: null;
    }

    public static function findById(int $id): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM roles WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $role = $stmt->fetch();
        return $role ?: null;
    }

    public static function defaultUserRoleId(): int
    {
        return self::idFor('user');
    }

    public static function idFor(string $name): int
    {
        $role = self::findByName($name);
        if (!$role) {
            throw new \RuntimeException(sprintf('Role "%s" não encontrada.', $name));
        }
        return $role->id;
    }
}
