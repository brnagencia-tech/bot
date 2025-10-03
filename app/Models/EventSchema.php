<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class EventSchema
{
    public int $id;
    public int $tenant_id;
    public string $event_name;
    public int $version;
    public string $json_schema;
    public string $status;
    public ?string $description;
    public string $created_at;
    public ?string $updated_at;

    public static function findActive(int $tenantId, string $eventName): ?self
    {
        $stmt = DB::getInstance()->prepare(
            'SELECT * FROM event_schemas WHERE tenant_id = ? AND event_name = ? AND status = "active" ORDER BY version DESC LIMIT 1'
        );
        $stmt->execute([$tenantId, $eventName]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $schema = $stmt->fetch();
        return $schema ?: null;
    }

    public function decoded(): array
    {
        $decoded = json_decode($this->json_schema, true);
        return is_array($decoded) ? $decoded : [];
    }
}
