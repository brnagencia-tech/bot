<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Event
{
    public int $id;
    public int $tenant_id;
    public ?int $pixel_id;
    public string $event_name;
    public string $event_time;
    public string $event_idempotency;
    public ?string $user_ids_json;
    public string $payload_json;
    public ?string $context_json;
    public string $status;
    public ?string $dest_status_json;
    public string $created_at;
    public ?string $updated_at;

    /**
     * @param array{
     *  tenant_id:int,
     *  pixel_id?:int|null,
     *  event_name:string,
     *  event_time:string,
     *  event_idempotency:string,
     *  user_ids?:array|null,
     *  payload:array,
     *  context?:array|null,
     *  status?:string,
     *  dest_status?:array|null
     * } $data
     */
    public static function create(array $data): self
    {
        $pdo = DB::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO events (tenant_id, pixel_id, event_name, event_time, event_idempotency, user_ids_json, payload_json, context_json, status, dest_status_json, created_at, updated_at)
             VALUES (:tenant_id, :pixel_id, :event_name, :event_time, :event_idempotency, :user_ids_json, :payload_json, :context_json, :status, :dest_status_json, NOW(), NOW())'
        );

        $status = $data['status'] ?? 'queued';
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':pixel_id' => $data['pixel_id'] ?? null,
            ':event_name' => $data['event_name'],
            ':event_time' => $data['event_time'],
            ':event_idempotency' => $data['event_idempotency'],
            ':user_ids_json' => isset($data['user_ids']) ? json_encode($data['user_ids'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ':payload_json' => json_encode($data['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':context_json' => isset($data['context']) ? json_encode($data['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ':status' => $status,
            ':dest_status_json' => isset($data['dest_status']) ? json_encode($data['dest_status'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);

        $id = (int) $pdo->lastInsertId();
        $event = self::findById($id);
        if (!$event) {
            throw new \RuntimeException('Falha ao registrar evento.');
        }
        return $event;
    }

    public static function findById(int $id): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $event = $stmt->fetch();
        return $event ?: null;
    }

    public static function findByIdempotency(int $tenantId, string $eventId): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM events WHERE tenant_id = ? AND event_idempotency = ? LIMIT 1');
        $stmt->execute([$tenantId, $eventId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $event = $stmt->fetch();
        return $event ?: null;
    }

    /**
     * @param array|null $statusDetails
     */
    public function markStatus(string $status, ?array $statusDetails = null): void
    {
        $payload = $statusDetails ? json_encode($statusDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

        $stmt = DB::getInstance()->prepare('UPDATE events SET status = ?, dest_status_json = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([
            $status,
            $payload,
            $this->id,
        ]);

        $this->status = $status;
        $this->dest_status_json = $payload;
    }

    public function toArray(bool $decodeJson = true): array
    {
        $data = [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'pixel_id' => $this->pixel_id,
            'event_name' => $this->event_name,
            'event_time' => $this->event_time,
            'event_idempotency' => $this->event_idempotency,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($decodeJson) {
            $data['user_ids'] = $this->user_ids_json ? json_decode($this->user_ids_json, true) : null;
            $data['payload'] = $this->payload_json ? json_decode($this->payload_json, true) : null;
            $data['context'] = $this->context_json ? json_decode($this->context_json, true) : null;
            $data['dest_status'] = $this->dest_status_json ? json_decode($this->dest_status_json, true) : null;
        } else {
            $data['user_ids_json'] = $this->user_ids_json;
            $data['payload_json'] = $this->payload_json;
            $data['context_json'] = $this->context_json;
            $data['dest_status_json'] = $this->dest_status_json;
        }

        return $data;
    }
}
