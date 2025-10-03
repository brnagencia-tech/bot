<?php
namespace App\Services;

use App\Models\Delivery;
use App\Models\Tenant;
use App\Models\Webhook;
use DomainException;

class WebhookService
{
    public function list(Tenant $tenant): array
    {
        $records = Webhook::listByTenant($tenant->id);
        return array_map(static fn(Webhook $webhook) => $webhook->toArray(), $records);
    }

    public function create(Tenant $tenant, array $data): array
    {
        $url = $this->sanitizeUrl($data['url'] ?? null);
        if (!$url) {
            throw new DomainException('validation_error');
        }
        $headers = $this->normalizeHeaders($data['headers'] ?? []);
        $secret = $data['secret'] ?? bin2hex(random_bytes(16));
        $secretEnc = hash('sha256', $secret);

        $webhook = Webhook::create($tenant->id, [
            'url' => $url,
            'secret_enc' => $secretEnc,
            'headers' => $headers,
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        $result = $webhook->toArray();
        $result['secret'] = $secret;
        return $result;
    }

    public function update(Tenant $tenant, int $webhookId, array $data): array
    {
        $webhook = $this->ensureOwnership($tenant, $webhookId);
        $payload = [];
        if (array_key_exists('url', $data)) {
            $url = $this->sanitizeUrl($data['url']);
            if (!$url) {
                throw new DomainException('validation_error');
            }
            $payload['url'] = $url;
        }
        if (array_key_exists('headers', $data)) {
            $payload['headers'] = $this->normalizeHeaders($data['headers']);
        }
        if (array_key_exists('is_active', $data)) {
            $payload['is_active'] = (bool) $data['is_active'];
        }
        if ($payload) {
            Webhook::updateAttributes($webhookId, $payload);
        }
        return Webhook::findById($webhookId)?->toArray() ?? $webhook->toArray();
    }

    public function delete(Tenant $tenant, int $webhookId): void
    {
        $this->ensureOwnership($tenant, $webhookId);
        Webhook::delete($webhookId);
    }

    public function rotateSecret(Tenant $tenant, int $webhookId): array
    {
        $webhook = $this->ensureOwnership($tenant, $webhookId);
        $secret = bin2hex(random_bytes(16));
        Webhook::updateAttributes($webhookId, []); // ensure updated_at touched later
        $stmt = \App\Core\DB::getInstance()->prepare('UPDATE webhooks SET secret_enc = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([hash('sha256', $secret), $webhookId]);
        $webhook->secret_enc = hash('sha256', $secret);
        $webhook->updated_at = gmdate('Y-m-d H:i:s');
        $data = $webhook->toArray();
        $data['secret'] = $secret;
        return $data;
    }

    public function enqueueDeliveries(int $tenantId, int $eventId): int
    {
        $webhooks = Webhook::listByTenant($tenantId);
        $count = 0;
        foreach ($webhooks as $webhook) {
            if (!$webhook->is_active) {
                continue;
            }
            Delivery::create($webhook->id, $eventId);
            $count++;
        }
        return $count;
    }

    public function runPendingDeliveries(): array
    {
        $batch = Delivery::pendingBatch(25);
        $results = [];
        foreach ($batch as $delivery) {
            // TODO: Implement real HTTP POST; por enquanto marca como entregue.
            $delivery->markSuccess();
            $results[] = $delivery->toArray();
        }
        return $results;
    }

    private function ensureOwnership(Tenant $tenant, int $webhookId): Webhook
    {
        $webhook = Webhook::findById($webhookId);
        if (!$webhook) {
            throw new DomainException('webhook_not_found');
        }
        if ((int) $webhook->tenant_id !== $tenant->id) {
            throw new DomainException('tenant_mismatch');
        }
        return $webhook;
    }

    private function sanitizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        return $url;
    }

    private function normalizeHeaders($headers): array
    {
        if (!is_array($headers)) {
            return [];
        }
        $normalized = [];
        foreach ($headers as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }
            $normalized[$key] = (string) $value;
        }
        return $normalized;
    }
}
