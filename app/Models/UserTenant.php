<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class UserTenant
{
    public int $id;
    public int $user_id;
    public int $tenant_id;
    public ?string $role_override;
    public string $created_at;
    public ?string $updated_at;

    public static function attach(int $userId, int $tenantId, ?string $roleOverride = null): void
    {
        $stmt = DB::getInstance()->prepare(
            'INSERT INTO user_tenant (user_id, tenant_id, role_override, created_at, updated_at)
             VALUES (?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE role_override = VALUES(role_override), updated_at = VALUES(updated_at)'
        );
        $stmt->execute([$userId, $tenantId, $roleOverride]);
    }

    public static function exists(int $userId, int $tenantId): bool
    {
        $stmt = DB::getInstance()->prepare('SELECT id FROM user_tenant WHERE user_id = ? AND tenant_id = ? LIMIT 1');
        $stmt->execute([$userId, $tenantId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * @return array<int,array{tenant_id:int,role_override:?string}>
     */
    public static function listTenants(int $userId): array
    {
        $stmt = DB::getInstance()->prepare('SELECT tenant_id, role_override FROM user_tenant WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array{tenant_id:int,role_override:?string}|null
     */
    public static function findMembership(int $userId, int $tenantId): ?array
    {
        $stmt = DB::getInstance()->prepare('SELECT tenant_id, role_override FROM user_tenant WHERE user_id = ? AND tenant_id = ? LIMIT 1');
        $stmt->execute([$userId, $tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return [
            'tenant_id' => (int) $row['tenant_id'],
            'role_override' => $row['role_override'] !== null ? (string) $row['role_override'] : null,
        ];
    }
}
