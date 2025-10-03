<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class PixelToken
{
    public int $id;
    public int $pixel_id;
    public string $token_enc;
    public ?string $last_used_at;
    public string $created_at;
    public ?string $updated_at;

    public static function verify(int $pixelId, string $token): ?self
    {
        $hash = self::hash($token);
        $stmt = DB::getInstance()->prepare('SELECT * FROM pixel_tokens WHERE pixel_id = ? AND token_enc = ? LIMIT 1');
        $stmt->execute([$pixelId, $hash]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $record = $stmt->fetch();
        if ($record) {
            self::touch((int) $record->id);
            return $record;
        }
        return null;
    }

    public static function touch(int $tokenId): void
    {
        $stmt = DB::getInstance()->prepare('UPDATE pixel_tokens SET last_used_at = NOW(), updated_at = NOW() WHERE id = ?');
        $stmt->execute([$tokenId]);
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * @return array{id:int,token:string}
     */
    public static function issue(int $pixelId, ?string $rawToken = null): array
    {
        $rawToken = $rawToken ?? bin2hex(random_bytes(32));
        $hash = self::hash($rawToken);

        $stmt = DB::getInstance()->prepare(
            'INSERT INTO pixel_tokens (pixel_id, token_enc, created_at, updated_at)
             VALUES (?, ?, NOW(), NOW())'
        );
        $stmt->execute([$pixelId, $hash]);

        $id = (int) DB::getInstance()->lastInsertId();

        return [
            'id' => $id,
            'token' => $rawToken,
        ];
    }

    /**
     * @return array<int,array{id:int,last_used_at:?string,created_at:string,updated_at:?string}>
     */
    public static function listForPixel(int $pixelId): array
    {
        $stmt = DB::getInstance()->prepare('SELECT id, last_used_at, created_at, updated_at FROM pixel_tokens WHERE pixel_id = ? ORDER BY created_at DESC');
        $stmt->execute([$pixelId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(static function ($row) {
            return [
                'id' => (int) $row['id'],
                'last_used_at' => $row['last_used_at'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];
        }, $rows);
    }

    public static function delete(int $tokenId): void
    {
        $stmt = DB::getInstance()->prepare('DELETE FROM pixel_tokens WHERE id = ?');
        $stmt->execute([$tokenId]);
    }
}
