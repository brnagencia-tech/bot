<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartRecoveryJob;
use App\Models\Client;
use App\Services\CartRecoveryService;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly CartRecoveryService $recoveryService
    ) {
    }

    public function home(): RedirectResponse
    {
        $user = Auth::user();

        return match ($user->role) {
            UserRole::MASTER => redirect()->route('master.dashboard'),
            UserRole::ADMIN => redirect()->route('admin.dashboard'),
            UserRole::STAFF => redirect()->route('staff.dashboard'),
        };
    }

    public function master(): View
    {
        $metrics = $this->dashboardService->masterMetrics();
        $clients = $this->dashboardService->clientsSnapshot();

        $usersByClient = Client::with('users')->get()->mapWithKeys(fn ($client) => [
            $client->id => $client->users,
        ]);

        return view('dashboard.master', [
            'metrics' => $metrics,
            'clients' => $clients,
            'usersByClient' => $usersByClient,
        ]);
    }

    public function admin(Request $request): View
    {
        $user = Auth::user();

        $client = match ($user->role) {
            UserRole::MASTER => Client::findOrFail((int) $request->input('client_id', $user->client_id ?? 0)),
            UserRole::ADMIN, UserRole::STAFF => $user->client,
        };

        abort_unless($client, 404, 'Cliente não encontrado');

        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,paid,abandoned,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $cartsQuery = Cart::query()->where('client_id', $client->id)->latest();

        if ($filters['status'] ?? null) {
            $cartsQuery->where('status', $filters['status']);
        }

        if ($filters['from'] ?? null) {
            $cartsQuery->whereDate('created_at', '>=', $filters['from']);
        }

        if ($filters['to'] ?? null) {
            $cartsQuery->whereDate('created_at', '<=', $filters['to']);
        }

        $carts = $cartsQuery->paginate(10)->withQueryString();

        $jobs = $client->recoveryJobs()->with('cart')->latest('scheduled_at')->limit(10)->get();

        $metrics = $this->dashboardService->clientMetrics($client);
        $funnel = Arr::get($client->config_json, 'funnel', []);

        return view('dashboard.admin', [
            'client' => $client,
            'metrics' => $metrics,
            'carts' => $carts,
            'jobs' => $jobs,
            'funnel' => $funnel,
            'filters' => $filters,
        ]);
    }

    public function staff(): View
    {
        $user = Auth::user();
        $client = $user->role === UserRole::MASTER ? null : $user->client;

        abort_unless($client, 404, 'Cliente não encontrado');

        $jobs = CartRecoveryJob::query()
            ->forClient($client->id)
            ->whereDate('scheduled_at', Carbon::today())
            ->with('cart')
            ->orderBy('scheduled_at')
            ->get()
            ->map(function (CartRecoveryJob $job) use ($client) {
                return [
                    'model' => $job,
                    'resume_url' => $this->recoveryService->buildResumeLink($job->cart, $client, $job->step),
                ];
            });

        return view('dashboard.staff', [
            'client' => $client,
            'jobs' => $jobs,
        ]);
    }
}
