<?php
namespace App\Controllers\Api;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenant;
use App\Services\AuthService;
use RuntimeException;

abstract class Controller
{
    protected function json(array $payload, int $status = 200): void
    {
        json_response($payload, $status);
    }

    protected function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    /**
     * @return array<string,mixed>
     */
    protected function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false) {
            throw new RuntimeException('Unable to read request body');
        }
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json([
                'error' => 'invalid_json',
                'message' => 'Corpo da requisição deve ser JSON válido.'
            ], 400);
        }
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string,mixed> $body
     * @param array<int,string> $fields
     */
    protected function assertRequired(array $body, array $fields): void
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $body) || $body[$field] === '' || $body[$field] === null) {
                $missing[] = $field;
            }
        }
        if ($missing) {
            $this->json([
                'error' => 'validation_error',
                'message' => 'Campos obrigatórios ausentes.',
                'fields' => $missing,
            ], 422);
        }
    }

    protected function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if ($header === '') {
            return null;
        }
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    protected function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? null;
    }

    /**
     * @return array<string,mixed>
     */
    protected function requestContext(): array
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        if (is_string($ip) && str_contains($ip, ',')) {
            $ip = explode(',', $ip)[0];
        }
        return [
            'client_ip_address' => $ip,
            'client_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null,
        ];
    }

    protected function requireUser(): User
    {
        $token = $this->bearerToken();
        if (!$token) {
            $this->json([
                'error' => 'unauthorized',
                'message' => 'Token não informado.'
            ], 401);
        }
        $service = new AuthService();
        $user = $service->findByToken($token);
        if (!$user) {
            $this->json([
                'error' => 'unauthorized',
                'message' => 'Token inválido.'
            ], 401);
        }
        return $user;
    }

    /**
     * @return array{tenant:Tenant,role:string}
     */
    protected function requireTenant(User $user, bool $adminRequired = false): array
    {
        $tenantHeader = $this->header('X-BRN-Tenant') ?? $_GET['tenant_id'] ?? null;
        if ($tenantHeader === null || !preg_match('/^\d+$/', (string) $tenantHeader)) {
            $this->json([
                'error' => 'tenant_required',
                'message' => 'Informe o tenant via header X-BRN-Tenant.'
            ], 400);
        }
        $tenantId = (int) $tenantHeader;
        $tenant = Tenant::findById($tenantId);
        if (!$tenant) {
            $this->json([
                'error' => 'tenant_not_found'
            ], 404);
        }

        $membership = UserTenant::findMembership($user->id, $tenantId);
        $roleName = Role::findById($user->role_id)?->name;

        if (!$membership && $roleName !== 'master') {
            $this->json([
                'error' => 'tenant_forbidden'
            ], 403);
        }

        $roleOverride = $membership['role_override'] ?? null;
        $effectiveRole = $roleOverride ?? 'user';
        if ($roleName === 'master') {
            $effectiveRole = 'master';
        }

        if ($adminRequired && !in_array($effectiveRole, ['admin', 'master'], true)) {
            $this->json([
                'error' => 'tenant_admin_required'
            ], 403);
        }

        return [
            'tenant' => $tenant,
            'role' => $effectiveRole,
        ];
    }
}
