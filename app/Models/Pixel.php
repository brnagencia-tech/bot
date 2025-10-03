<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Pixel
{
    public int $id;
    public int $tenant_id;
    public string $name;
    public string $pixel_id;
    public ?string $description;
    public bool $is_active;
    public ?string $config_json;
    public string $created_at;
    public ?string $updated_at;

    public static function findByPublicId(string $pixelId): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM pixels WHERE pixel_id = ? LIMIT 1');
        $stmt->execute([$pixelId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $pixel = $stmt->fetch();
        return $pixel ?: null;
    }

    public static function findById(int $id): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM pixels WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $pixel = $stmt->fetch();
        return $pixel ?: null;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * @return array<int,self>
     */
    public static function listByTenant(int $tenantId): array
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM pixels WHERE tenant_id = ? ORDER BY created_at DESC');
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    /**
     * @param array{name:string,pixel_id:string,description?:string|null,config?:array|null,is_active?:bool} $data
     */
    public static function createForTenant(int $tenantId, array $data): self
    {
        $pdo = DB::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO pixels (tenant_id, name, pixel_id, description, is_active, config_json, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );

        $configJson = null;
        if (isset($data['config']) && is_array($data['config'])) {
            $configJson = json_encode($data['config'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $stmt->execute([
            $tenantId,
            $data['name'],
            $data['pixel_id'],
            $data['description'] ?? null,
            isset($data['is_active']) ? (int) $data['is_active'] : 1,
            $configJson,
        ]);

        $id = (int) $pdo->lastInsertId();
        $pixel = self::findById($id);
        if (!$pixel) {
            throw new \RuntimeException('Falha ao criar pixel.');
        }
        return $pixel;
    }

    /**
     * @param array{name?:string,description?:string|null,is_active?:bool,config?:array|null} $attributes
     */
    public static function updateAttributes(int $id, array $attributes): void
    {
        if (!$attributes) {
            return;
        }

        $columns = [];
        $values = [];

        if (array_key_exists('name', $attributes)) {
            $columns[] = 'name = ?';
            $values[] = $attributes['name'];
        }
        if (array_key_exists('description', $attributes)) {
            $columns[] = 'description = ?';
            $values[] = $attributes['description'];
        }
        if (array_key_exists('is_active', $attributes)) {
            $columns[] = 'is_active = ?';
            $values[] = $attributes['is_active'] ? 1 : 0;
        }
        if (array_key_exists('config', $attributes)) {
            $columns[] = 'config_json = ?';
            $values[] = is_array($attributes['config'])
                ? json_encode($attributes['config'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null;
        }

        if (!$columns) {
            return;
        }

        $columns[] = 'updated_at = NOW()';
        $sql = 'UPDATE pixels SET ' . implode(', ', $columns) . ' WHERE id = ?';
        $values[] = $id;

        $stmt = DB::getInstance()->prepare($sql);
        $stmt->execute($values);
    }

    public function toArray(bool $includeConfig = false): array
    {
        $data = [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'pixel_id' => $this->pixel_id,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($includeConfig) {
            $data['config'] = $this->config_json ? json_decode($this->config_json, true) : null;
        }

        return $data;
    }
}
