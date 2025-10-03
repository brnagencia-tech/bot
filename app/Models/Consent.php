<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Consent
{
    public int $id;
    public int $tenant_id;
    public string $user_anon_id;
    public string $policy_version;
    public string $purposes_json;
    public string $granted_at;
    public ?string $revoked_at;
    public ?string $meta_json;
    public string $created_at;
    public ?string $updated_at;

    /**
     * @param array{
     *  tenant_id:int,
     *  user_anon_id:string,
     *  policy_version:string,
     *  purposes:array,
     *  granted_at:?string,
     *  revoked_at:?string,
     *  meta?:array|null
     * } $data
     */
    public static function create(array $data): self
    {
        $pdo = DB::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO consents (tenant_id, user_anon_id, policy_version, purposes_json, granted_at, revoked_at, meta_json, created_at, updated_at)
             VALUES (:tenant_id, :user_anon_id, :policy_version, :purposes_json, :granted_at, :revoked_at, :meta_json, NOW(), NOW())'
        );
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':user_anon_id' => $data['user_anon_id'],
            ':policy_version' => $data['policy_version'],
            ':purposes_json' => json_encode($data['purposes'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':granted_at' => $data['granted_at'] ?? gmdate('Y-m-d H:i:s'),
            ':revoked_at' => $data['revoked_at'] ?? null,
            ':meta_json' => isset($data['meta']) ? json_encode($data['meta'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);

        $id = (int) $pdo->lastInsertId();
        $consent = self::findById($id);
        if (!$consent) {
            throw new \RuntimeException('Falha ao registrar consentimento.');
        }
        return $consent;
    }

    public static function findById(int $id): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM consents WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $consent = $stmt->fetch();
        return $consent ?: null;
    }

    public static function markRevoked(int $id, ?array $meta = null): void
    {
        $stmt = DB::getInstance()->prepare('UPDATE consents SET revoked_at = NOW(), meta_json = COALESCE(:meta_json, meta_json), updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':meta_json' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    }

    /**
     * @return array<int,self>
     */
    public static function listByTenant(int $tenantId, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $conditions = ['tenant_id = :tenant_id'];
        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['user_anon_id'])) {
            $conditions[] = 'user_anon_id = :user_anon_id';
            $params[':user_anon_id'] = $filters['user_anon_id'];
        }
        if (!empty($filters['policy_version'])) {
            $conditions[] = 'policy_version = :policy_version';
            $params[':policy_version'] = $filters['policy_version'];
        }
        if (array_key_exists('active', $filters)) {
            if ($filters['active']) {
                $conditions[] = 'revoked_at IS NULL';
            } else {
                $conditions[] = 'revoked_at IS NOT NULL';
            }
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT * FROM consents WHERE $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = DB::getInstance()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function countByTenant(int $tenantId, array $filters = []): int
    {
        $conditions = ['tenant_id = :tenant_id'];
        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['user_anon_id'])) {
            $conditions[] = 'user_anon_id = :user_anon_id';
            $params[':user_anon_id'] = $filters['user_anon_id'];
        }
        if (!empty($filters['policy_version'])) {
            $conditions[] = 'policy_version = :policy_version';
            $params[':policy_version'] = $filters['policy_version'];
        }
        if (array_key_exists('active', $filters)) {
            if ($filters['active']) {
                $conditions[] = 'revoked_at IS NULL';
            } else {
                $conditions[] = 'revoked_at IS NOT NULL';
            }
        }

        $where = implode(' AND ', $conditions);
        $stmt = DB::getInstance()->prepare("SELECT COUNT(*) FROM consents WHERE $where");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'user_anon_id' => $this->user_anon_id,
            'policy_version' => $this->policy_version,
            'purposes' => json_decode($this->purposes_json, true),
            'granted_at' => $this->granted_at,
            'revoked_at' => $this->revoked_at,
            'meta' => $this->meta_json ? json_decode($this->meta_json, true) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
