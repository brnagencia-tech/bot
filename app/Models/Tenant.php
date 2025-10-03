<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Tenant
{
    public int $id;
    public string $name;
    public string $status;
    public string $created_at;
    public ?string $updated_at;

    public static function findById(int $id): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM tenants WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $tenant = $stmt->fetch();
        return $tenant ?: null;
    }

    public static function findByName(string $name): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM tenants WHERE name = ? LIMIT 1');
        $stmt->execute([$name]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $tenant = $stmt->fetch();
        return $tenant ?: null;
    }

    public static function create(string $name, string $status = 'active'): self
    {
        $stmt = DB::getInstance()->prepare('INSERT INTO tenants (name, status, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        $stmt->execute([$name, $status]);
        $id = (int) DB::getInstance()->lastInsertId();
        $tenant = self::findById($id);
        if (!$tenant) {
            throw new \RuntimeException('Falha ao criar tenant.');
        }
        return $tenant;
    }
}
