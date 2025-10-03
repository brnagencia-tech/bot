<?php
namespace App\Services;

use App\Core\DB;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenant;

class AuthService
{
    /**
     * @param array{name?:string|null,email:string,password:string,tenant_name?:string|null,tenant_id?:int|null} $data
     * @return array<string,mixed>
     */
    public function register(array $data): array
    {
        $pdo = DB::getInstance();

        if (User::findByEmail($data['email'])) {
            throw new \DomainException('email_already_registered');
        }

        $pdo->beginTransaction();
        try {
            $tenant = $this->resolveTenantForRegistration($data);
            $roleId = Role::idFor($tenant['user_role']);

            $user = User::create([
                'role_id' => $roleId,
                'name' => $data['name'] ?? null,
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            UserTenant::attach($user->id, $tenant['model']->id, $tenant['role_override']);

            $token = $this->issueToken($user->id);
            $pdo->commit();

            return [
                'token' => $token,
                'user' => $user->clearSensitive(),
                'tenant' => [
                    'id' => $tenant['model']->id,
                    'name' => $tenant['model']->name,
                    'role_override' => $tenant['role_override'],
                ],
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @return array{model:Tenant,role_override:?string,user_role:string}
     */
    private function resolveTenantForRegistration(array $data): array
    {
        if (!empty($data['tenant_id'])) {
            $tenant = Tenant::findById((int) $data['tenant_id']);
            if (!$tenant) {
                throw new \DomainException('tenant_not_found');
            }
            return [
                'model' => $tenant,
                'role_override' => 'user',
                'user_role' => 'user',
            ];
        }

        $tenantName = trim($data['tenant_name'] ?? '');
        if ($tenantName === '') {
            throw new \DomainException('tenant_name_required');
        }
        if (Tenant::findByName($tenantName)) {
            throw new \DomainException('tenant_name_in_use');
        }
        $tenant = Tenant::create($tenantName);
        return [
            'model' => $tenant,
            'role_override' => 'admin',
            'user_role' => 'admin',
        ];
    }

    public function login(string $email, string $password): array
    {
        $user = User::findByEmail($email);
        if (!$user || !$user->verifyPassword($password)) {
            throw new \DomainException('invalid_credentials');
        }

        $tenants = UserTenant::listTenants($user->id);
        if (!$tenants) {
            throw new \DomainException('user_without_tenant');
        }

        $token = $this->issueToken($user->id);

        return [
            'token' => $token,
            'user' => $user->clearSensitive(),
            'tenants' => $tenants,
        ];
    }

    public function logout(User $user): void
    {
        User::setRememberToken($user->id, null);
    }

    private function issueToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        User::setRememberToken($userId, hash('sha256', $token));
        return $token;
    }

    public function findByToken(string $token): ?User
    {
        $hash = hash('sha256', $token);
        $stmt = DB::getInstance()->prepare('SELECT id FROM users WHERE remember_token = ? LIMIT 1');
        $stmt->execute([$hash]);
        $userId = $stmt->fetchColumn();
        return $userId ? User::findById((int) $userId) : null;
    }
}
