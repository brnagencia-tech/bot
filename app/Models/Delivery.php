<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Delivery
{
    public int $id;
    public int $webhook_id;
    public int $event_id;
    public string $status;
    public int $attempts;
    public ?string $last_error;
    public ?string $last_attempt_at;
    public ?string $next_attempt_at;
    public string $created_at;
    public ?string $updated_at;

    public static function create(int $webhookId, int $eventId, string $status = 'pending'): self
    {
        $pdo = DB::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO deliveries (webhook_id, event_id, status, attempts, created_at, updated_at)
             VALUES (?, ?, ?, 0, NOW(), NOW())'
        );
        $stmt->execute([$webhookId, $eventId, $status]);
        $id = (int) $pdo->lastInsertId();
        $delivery = self::findById($id);
        if (!$delivery) {
            throw new \RuntimeException('Falha ao criar entrega.');
        }
        return $delivery;
    }

    public static function findById(int $id): ?self
    {
        $stmt = DB::getInstance()->prepare('SELECT * FROM deliveries WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $delivery = $stmt->fetch();
        return $delivery ?: null;
    }

    /**
     * @return array<int,self>
     */
    public static function pendingBatch(int $limit = 50): array
    {
        $stmt = DB::getInstance()->prepare(
            'SELECT * FROM deliveries WHERE status IN ("pending", "retrying") AND (next_attempt_at IS NULL OR next_attempt_at <= NOW()) ORDER BY created_at ASC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public function markSuccess(): void
    {
        $stmt = DB::getInstance()->prepare(
            'UPDATE deliveries SET status = "delivered", attempts = attempts + 1, last_error = NULL, last_attempt_at = NOW(), updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$this->id]);
        $this->status = 'delivered';
    }

    public function markFailure(string $error, int $retrySeconds = 60): void
    {
        $stmt = DB::getInstance()->prepare(
            'UPDATE deliveries SET status = "retrying", attempts = attempts + 1, last_error = ?, last_attempt_at = NOW(), next_attempt_at = DATE_ADD(NOW(), INTERVAL ? SECOND), updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$error, $retrySeconds, $this->id]);
        $this->status = 'retrying';
        $this->last_error = $error;
    }

    public function markDead(string $error): void
    {
        $stmt = DB::getInstance()->prepare(
            'UPDATE deliveries SET status = "dead", attempts = attempts + 1, last_error = ?, last_attempt_at = NOW(), updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$error, $this->id]);
        $this->status = 'dead';
        $this->last_error = $error;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'webhook_id' => $this->webhook_id,
            'event_id' => $this->event_id,
            'status' => $this->status,
            'attempts' => $this->attempts,
            'last_error' => $this->last_error,
            'last_attempt_at' => $this->last_attempt_at,
            'next_attempt_at' => $this->next_attempt_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
