<?php
namespace App\Services;

use App\Models\Consent;
use App\Models\Pixel;
use App\Models\PixelToken;
use App\Models\Tenant;
use DomainException;

class ConsentService
{
    /**
     * @param array<string,mixed> $data
     */
    public function recordViaPixel(string $pixelPublicId, string $token, array $data): array
    {
        $pixel = Pixel::findByPublicId($pixelPublicId);
        if (!$pixel || !$pixel->isActive()) {
            throw new DomainException('pixel_not_found_or_inactive');
        }
        if (!PixelToken::verify($pixel->id, $token)) {
            throw new DomainException('invalid_pixel_token');
        }

        $anonymousId = $this->stringValue($data['anonymous_id'] ?? null);
        $policyVersion = $this->stringValue($data['policy_version'] ?? null);
        if (!$anonymousId || !$policyVersion) {
            throw new DomainException('validation_error');
        }

        $purposes = $this->normalizePurposes($data['purposes'] ?? []);
        $granted = !isset($data['granted']) || (bool) $data['granted'];

        $meta = $this->normalizeMeta($data['meta'] ?? []);
        if (!isset($meta['source'])) {
            $meta['source'] = 'pixel_sdk';
        }

        $consent = Consent::create([
            'tenant_id' => $pixel->tenant_id,
            'user_anon_id' => $anonymousId,
            'policy_version' => $policyVersion,
            'purposes' => $purposes,
            'granted_at' => gmdate('Y-m-d H:i:s'),
            'revoked_at' => $granted ? null : gmdate('Y-m-d H:i:s'),
            'meta' => $meta,
        ]);

        return $consent->toArray();
    }

    /**
     * @return array{data:array<int,array<string,mixed>>,pagination:array{page:int,per_page:int,total:int}}
     */
    public function list(Tenant $tenant, array $filters = []): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 50)));
        $offset = ($page - 1) * $perPage;

        $queryFilters = $filters;
        unset($queryFilters['page'], $queryFilters['per_page']);
        $records = Consent::listByTenant($tenant->id, $queryFilters, $perPage, $offset);
        $total = Consent::countByTenant($tenant->id, $queryFilters);

        return [
            'data' => array_map(static fn(Consent $consent) => $consent->toArray(), $records),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ];
    }

    public function revoke(Tenant $tenant, int $consentId, ?array $meta = null): void
    {
        $consent = Consent::findById($consentId);
        if (!$consent) {
            throw new DomainException('consent_not_found');
        }
        if ((int) $consent->tenant_id !== $tenant->id) {
            throw new DomainException('tenant_mismatch');
        }
        Consent::markRevoked($consentId, $meta);
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            $value = trim($value);
            return $value !== '' ? $value : null;
        }
        if (is_numeric($value)) {
            return (string) $value;
        }
        return null;
    }

    /**
     * @param mixed $purposes
     * @return array<string,bool>
     */
    private function normalizePurposes($purposes): array
    {
        if (is_array($purposes)) {
            $result = [];
            foreach ($purposes as $key => $value) {
                if (is_string($key)) {
                    $result[$key] = $this->toBool($value);
                } elseif (is_string($value)) {
                    $result[$value] = true;
                }
            }
            return $result;
        }
        if (is_string($purposes)) {
            return [$purposes => true];
        }
        return [];
    }

    /**
     * @param mixed $meta
     * @return array<string,mixed>
     */
    private function normalizeMeta($meta): array
    {
        if (!is_array($meta)) {
            return [];
        }
        return $meta;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (bool) $value;
        }
        if (is_string($value)) {
            $filtered = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($filtered !== null) {
                return $filtered;
            }
        }
        return (bool) $value;
    }
}
