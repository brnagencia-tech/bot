<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Webhook
{
    public int $id;
    public int $tenant_id;
    public string $url;
    public string $secret_enc;
    public ?string $headers_json;
    public int $is_active;
    public string $created_at;
    public ?string $updated_at;

    public static function create(int $tenantId, array $data): self
    {
        $pdo = DB::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO webhooks (tenant_id, url, secret_enc, headers_json, is_active, created_at, updated_at)
             VALUES (:tenant_id, :url, :secret_enc, :headers_json, :is_active, NOW(), NOW())'
        );
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':url' => $data['url'],
            ':secret_enc' => $data['secret_enc'],
            ':headers_json' => isset($data['headers']) ? json_encode($data['headers'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ':is_active' => $data['is_active'] ?? 1,
        ]);
        $id = (int) $pdo->lastInsertId();
        $webhook = self::findById($id);
        if (!$webhook) {
            throw new \RuntimeException('Falha ao criar webhook.');
        }
        return $webhook;
    }

    public static function findById(int $id): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM webhooks WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $webhook = $stmt->fetch();
        return $webhook ?: null;
    }

    /**
     * @return array<int,self>
     */
    public static function listByTenant(int $tenantId): array
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM webhooks WHERE tenant_id = ? ORDER BY created_at DESC');
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function updateAttributes(int $id, array $data): void
    {
        $columns = [];
        $values = [];
        if (array_key_exists('url', $data)) {
            $columns[] = 'url = ?';
            $values[] = $data['url'];
        }
        if (array_key_exists('headers', $data)) {
            $columns[] = 'headers_json = ?';
            $values[] = $data['headers'] ? json_encode($data['headers'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        }
        if (array_key_exists('is_active', $data)) {
            $columns[] = 'is_active = ?';
            $values[] = $data['is_active'] ? 1 : 0;
        }
        if (!$columns) {
            return;
        }
        $columns[] = 'updated_at = NOW()';
        $values[] = $id;
        $sql = 'UPDATE webhooks SET ' . implode(', ', $columns) . ' WHERE id = ?';
        $stmt = DB::getInstance()->prepare($sql);
        $stmt->execute($values);
    }

    public static function delete(int $id): void
    {
        $stmt = DB::getInstance()->prepare('DELETE FROM webhooks WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function toArray(bool $includeSecret = false): array
    {
        $data = [
            'id' => $this->id,
            'url' => $this->url,
            'headers' => $this->headers_json ? json_decode($this->headers_json, true) : null,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        if ($includeSecret) {
            $data['secret_enc'] = $this->secret_enc;
        }
        return $data;
    }
}
