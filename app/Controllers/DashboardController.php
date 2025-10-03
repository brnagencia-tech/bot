<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Tenant;
use App\Models\UserTenant;
use App\Services\EventService;

class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();
        $user = Auth::user();
        if (!$user) {
            redirect('/login');
        }

        $tenant = $this->resolveTenant($user);

        $service = new EventService();
        $metrics = $service->metrics($tenant, []);

        require __DIR__ . '/../Views/dashboard.php';
    }

    private function resolveTenant($user): Tenant
    {
        $tenantId = isset($_SESSION['tenant_id']) ? (int) $_SESSION['tenant_id'] : null;
        if (!$tenantId) {
            $tenants = UserTenant::listTenants($user->id);
            if (!$tenants) {
                throw new \RuntimeException('Usuário não associado a nenhum tenant.');
            }
            $tenantId = $tenants[0]['tenant_id'];
            $_SESSION['tenant_id'] = $tenantId;
        }
        $tenant = Tenant::findById($tenantId);
        if (!$tenant) {
            throw new \RuntimeException('Tenant não encontrado.');
        }
        return $tenant;
    }
}
