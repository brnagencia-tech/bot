<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class User
{
    public int $id;
    public int $role_id;
    public ?string $name;
    public string $email;
    public string $password_hash;
    public ?string $mfa_secret;
    public ?string $remember_token;
    public string $created_at;
    public ?string $updated_at;

    public static function findById(int $id): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * @param array{name?:string|null,email:string,password:string,role_id:int} $data
     */
    public static function create(array $data): self
    {
        $stmt = DB::getInstance()->prepare(
            'INSERT INTO users (role_id, name, email, password_hash, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['role_id'],
            $data['name'] ?? null,
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
        $id = (int) DB::getInstance()->lastInsertId();
        $user = self::findById($id);
        if (!$user) {
            throw new \RuntimeException('Falha ao criar usuário.');
        }
        return $user;
    }

    public static function updatePassword(int $id, string $password): bool
    {
        $stmt = DB::getInstance()->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([
            password_hash($password, PASSWORD_DEFAULT),
            $id,
        ]);
    }

    /**
     * @param array{name?:string|null} $data
     */
    public static function updateProfile(int $id, array $data): bool
    {
        $stmt = DB::getInstance()->prepare('UPDATE users SET name = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([
            $data['name'] ?? null,
            $id,
        ]);
    }

    public static function setRememberToken(int $id, ?string $token): bool
    {
        $stmt = DB::getInstance()->prepare('UPDATE users SET remember_token = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$token, $id]);
    }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, $this->password_hash);
    }

    public function clearSensitive(): array
    {
        return [
            'id' => $this->id,
            'role_id' => $this->role_id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
