<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function master(Request $request): JsonResponse
    {
        if ($request->user()->role !== UserRole::MASTER) {
            abort(403, 'Apenas usuários master podem acessar este painel.');
        }

        $metrics = $this->dashboardService->masterMetrics();
        $clients = $this->dashboardService->clientsSnapshot()->map(fn ($client) => [
            'id' => $client->id,
            'name' => $client->name,
            'active' => $client->is_active,
            'pending_carts' => $client->carts_pending_count,
            'paid_carts' => $client->carts_paid_count,
            'jobs_due' => $client->jobs_due_count,
        ])->values();

        return response()->json([
            'global' => $metrics,
            'clients' => $clients,
        ]);
    }

    public function client(Request $request, Client $client): JsonResponse
    {
        $user = $request->user();

        if ($user->role === UserRole::MASTER) {
            // ok
        } elseif (in_array($user->role, [UserRole::ADMIN, UserRole::STAFF], true)) {
            if ($user->client_id !== $client->id) {
                abort(403, 'Acesso negado para o cliente solicitado.');
            }
        } else {
            abort(403);
        }

        $metrics = $this->dashboardService->clientMetrics($client);

        return response()->json([
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'whatsapp_phone' => $client->whatsapp_phone,
                'campaign_paused' => $client->paused_at !== null,
            ],
            'metrics' => $metrics,
        ]);
    }
}
