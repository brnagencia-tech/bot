<?php
namespace App\Services;

use App\Models\Pixel;
use App\Models\PixelToken;
use App\Models\Tenant;
use DomainException;

class PixelService
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public function list(Tenant $tenant): array
    {
        $pixels = Pixel::listByTenant($tenant->id);
        return array_map(static function (Pixel $pixel) {
            return $pixel->toArray(true);
        }, $pixels);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(Tenant $tenant, array $data): array
    {
        $name = $this->sanitizeString($data['name'] ?? null);
        $pixelId = $this->sanitizeString($data['pixel_id'] ?? null);
        $description = $this->sanitizeNullable($data['description'] ?? null);
        $config = isset($data['config']) && is_array($data['config']) ? $data['config'] : null;

        if (!$name || !$pixelId) {
            throw new DomainException('validation_error');
        }

        $existing = Pixel::findByPublicId($pixelId);
        if ($existing) {
            throw new DomainException('pixel_id_in_use');
        }

        $pixel = Pixel::createForTenant($tenant->id, [
            'name' => $name,
            'pixel_id' => $pixelId,
            'description' => $description,
            'config' => $config,
        ]);

        return $pixel->toArray(true);
    }

    public function show(Tenant $tenant, int $pixelId): array
    {
        $pixel = Pixel::findById($pixelId);
        if (!$pixel) {
            throw new DomainException('pixel_not_found');
        }
        if ((int) $pixel->tenant_id !== $tenant->id) {
            throw new DomainException('tenant_mismatch');
        }

        $data = $pixel->toArray(true);
        $data['tokens'] = PixelToken::listForPixel($pixel->id);
        return $data;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(Tenant $tenant, int $pixelId, array $data): array
    {
        $pixel = Pixel::findById($pixelId);
        if (!$pixel) {
            throw new DomainException('pixel_not_found');
        }
        if ((int) $pixel->tenant_id !== $tenant->id) {
            throw new DomainException('tenant_mismatch');
        }

        $attributes = [];
        if (array_key_exists('name', $data)) {
            $name = $this->sanitizeString($data['name']);
            if (!$name) {
                throw new DomainException('validation_error');
            }
            $attributes['name'] = $name;
        }
        if (array_key_exists('description', $data)) {
            $attributes['description'] = $this->sanitizeNullable($data['description']);
        }
        if (array_key_exists('is_active', $data)) {
            $attributes['is_active'] = (bool) $data['is_active'];
        }
        if (array_key_exists('config', $data)) {
            $attributes['config'] = is_array($data['config']) ? $data['config'] : null;
        }

        Pixel::updateAttributes($pixelId, $attributes);
        $fresh = Pixel::findById($pixelId);
        if (!$fresh) {
            throw new DomainException('pixel_not_found');
        }
        return $fresh->toArray(true);
    }

    public function deactivate(Tenant $tenant, int $pixelId): void
    {
        $pixel = Pixel::findById($pixelId);
        if (!$pixel) {
            throw new DomainException('pixel_not_found');
        }
        if ((int) $pixel->tenant_id !== $tenant->id) {
            throw new DomainException('tenant_mismatch');
        }

        Pixel::updateAttributes($pixelId, ['is_active' => false]);
    }

    public function issueToken(Tenant $tenant, int $pixelId): array
    {
        $pixel = Pixel::findById($pixelId);
        if (!$pixel) {
            throw new DomainException('pixel_not_found');
        }
        if ((int) $pixel->tenant_id !== $tenant->id) {
            throw new DomainException('tenant_mismatch');
        }

        $issued = PixelToken::issue($pixel->id);
        return [
            'token_id' => $issued['id'],
            'token' => $issued['token'],
        ];
    }

    public function revokeToken(Tenant $tenant, int $pixelId, int $tokenId): void
    {
        $pixel = Pixel::findById($pixelId);
        if (!$pixel) {
            throw new DomainException('pixel_not_found');
        }
        if ((int) $pixel->tenant_id !== $tenant->id) {
            throw new DomainException('tenant_mismatch');
        }

        $tokens = PixelToken::listForPixel($pixel->id);
        $exists = false;
        foreach ($tokens as $token) {
            if ($token['id'] === $tokenId) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            throw new DomainException('token_not_found');
        }

        PixelToken::delete($tokenId);
    }

    private function sanitizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }

    private function sanitizeNullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }
}
