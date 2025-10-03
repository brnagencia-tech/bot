<?php
namespace App\Services;

use App\Core\DB;
use App\Models\Event;
use App\Models\Tenant;
use DomainException;
use PDO;

class EventService
{
    /**
     * @param array<string,mixed> $filters
     * @return array{data:array<int,array<string,mixed>>,pagination:array{page:int,per_page:int,total:int}}
     */
    public function list(Tenant $tenant, array $filters = []): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = (int) ($filters['per_page'] ?? 50);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        [$conditions, $params] = $this->buildConditions($tenant->id, $filters);
        $where = implode(' AND ', $conditions);

        $pdo = $this->pdo();
        $sql = 'SELECT e.*, p.pixel_id AS pixel_public_id, p.name AS pixel_name
                FROM events e
                LEFT JOIN pixels p ON e.pixel_id = p.id
                WHERE ' . $where . '
                ORDER BY e.event_time DESC
                LIMIT :limit OFFSET :offset';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array_map(function (array $row) {
            return $this->hydrateRow($row);
        }, $rows);

        $countSql = 'SELECT COUNT(*) FROM events e LEFT JOIN pixels p ON e.pixel_id = p.id WHERE ' . $where;
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ];
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function metrics(Tenant $tenant, array $filters = []): array
    {
        [$conditions, $params] = $this->buildConditions($tenant->id, $filters);
        $where = implode(' AND ', $conditions);

        $pdo = $this->pdo();
        $statusSql = 'SELECT e.status, COUNT(*) AS count
                      FROM events e
                      LEFT JOIN pixels p ON e.pixel_id = p.id
                      WHERE ' . $where . '
                      GROUP BY e.status';
        $stmt = $pdo->prepare($statusSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $byStatus = [
            'queued' => 0,
            'processing' => 0,
            'delivered' => 0,
            'failed' => 0,
            'dropped' => 0,
        ];
        $total = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $status = (string) $row['status'];
            $count = (int) $row['count'];
            $byStatus[$status] = $count;
            $total += $count;
        }

        $lastSql = 'SELECT MAX(e.event_time) AS last_event_time, MAX(e.created_at) AS last_created_at
                    FROM events e
                    LEFT JOIN pixels p ON e.pixel_id = p.id
                    WHERE ' . $where;
        $lastStmt = $pdo->prepare($lastSql);
        foreach ($params as $key => $value) {
            $lastStmt->bindValue($key, $value);
        }
        $lastStmt->execute();
        $lastRow = $lastStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $recentSql = 'SELECT COUNT(*)
                      FROM events e
                      LEFT JOIN pixels p ON e.pixel_id = p.id
                      WHERE ' . $where . ' AND e.event_time >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY)';
        $recentStmt = $pdo->prepare($recentSql);
        foreach ($params as $key => $value) {
            $recentStmt->bindValue($key, $value);
        }
        $recentStmt->execute();
        $last24h = (int) $recentStmt->fetchColumn();

        return [
            'total' => $total,
            'by_status' => $byStatus,
            'last_event_time' => $lastRow['last_event_time'] ?? null,
            'last_created_at' => $lastRow['last_created_at'] ?? null,
            'events_last_24h' => $last24h,
        ];
    }

    public function show(Tenant $tenant, int $eventId): array
    {
        $pdo = $this->pdo();
        $sql = 'SELECT e.*, p.pixel_id AS pixel_public_id, p.name AS pixel_name
                FROM events e
                LEFT JOIN pixels p ON e.pixel_id = p.id
                WHERE e.tenant_id = :tenant_id AND e.id = :id
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenant->id,
            ':id' => $eventId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new DomainException('event_not_found');
        }
        return $this->hydrateRow($row);
    }

    /**
     * @return array{conditions:array<int,string>,params:array<string,mixed>}
     */
    private function buildConditions(int $tenantId, array $filters): array
    {
        $conditions = ['e.tenant_id = :tenant_id'];
        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['status'])) {
            $conditions[] = 'e.status = :status';
            $params[':status'] = (string) $filters['status'];
        }

        if (!empty($filters['event_name'])) {
            $conditions[] = 'e.event_name LIKE :event_name';
            $params[':event_name'] = ((string) $filters['event_name']) . '%';
        }

        if (!empty($filters['pixel_id'])) {
            $conditions[] = 'e.pixel_id = :pixel_id';
            $params[':pixel_id'] = (int) $filters['pixel_id'];
        }

        if (!empty($filters['pixel_public_id'])) {
            $conditions[] = 'p.pixel_id = :pixel_public_id';
            $params[':pixel_public_id'] = (string) $filters['pixel_public_id'];
        }

        if (!empty($filters['from'])) {
            $from = $this->normalizeDate($filters['from']);
            if ($from) {
                $conditions[] = 'e.event_time >= :from';
                $params[':from'] = $from;
            }
        }

        if (!empty($filters['to'])) {
            $to = $this->normalizeDate($filters['to']);
            if ($to) {
                $conditions[] = 'e.event_time <= :to';
                $params[':to'] = $to;
            }
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $conditions[] = '(e.event_idempotency LIKE :search OR e.payload_json LIKE :search OR e.context_json LIKE :search)';
            $params[':search'] = $search;
        }

        return [$conditions, $params];
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_numeric($value)) {
            return gmdate('Y-m-d H:i:s', (int) $value);
        }
        if (is_string($value) && strtotime($value) !== false) {
            return gmdate('Y-m-d H:i:s', strtotime($value));
        }
        return null;
    }

    private function hydrateRow(array $row): array
    {
        $event = new Event();
        foreach ($row as $key => $value) {
            if (property_exists($event, $key)) {
                $event->$key = $value;
            }
        }
        $data = $event->toArray();
        $data['pixel'] = [
            'id' => $event->pixel_id,
            'pixel_id' => $row['pixel_public_id'] ?? null,
            'name' => $row['pixel_name'] ?? null,
        ];
        return $data;
    }

    private function pdo(): PDO
    {
        return DB::getInstance();
    }
}
