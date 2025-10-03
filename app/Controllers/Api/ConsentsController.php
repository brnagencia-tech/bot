<?php
namespace App\Controllers\Api;

use App\Services\ConsentService;
use DomainException;

class ConsentsController extends Controller
{
    private ConsentService $service;

    public function __construct()
    {
        $this->service = new ConsentService();
    }

    public function store(): void
    {
        $body = $this->getJsonBody();
        $this->assertRequired($body, ['pixel_id', 'anonymous_id', 'policy_version']);
        $token = $this->header('X-BRN-Pixel-Token') ?? $body['pixel_token'] ?? null;
        if (!$token) {
            $this->json([
                'error' => 'unauthorized',
                'message' => 'X-BRN-Pixel-Token obrigatório.'
            ], 401);
        }

        try {
            $result = $this->service->recordViaPixel((string) $body['pixel_id'], trim((string) $token), $body);
            $this->json($result, 201);
        } catch (DomainException $e) {
            $this->json([
                'error' => $e->getMessage(),
            ], $this->statusFor($e));
        }
    }

    public function index(): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        $filters = [];
        if (isset($_GET['user_anon_id']) && $_GET['user_anon_id'] !== '') {
            $filters['user_anon_id'] = (string) $_GET['user_anon_id'];
        }
        if (isset($_GET['policy_version']) && $_GET['policy_version'] !== '') {
            $filters['policy_version'] = (string) $_GET['policy_version'];
        }
        if (isset($_GET['active'])) {
            $active = filter_var($_GET['active'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($active !== null) {
                $filters['active'] = $active;
            }
        }
        $filters['page'] = $_GET['page'] ?? 1;
        $filters['per_page'] = $_GET['per_page'] ?? 50;

        $response = $this->service->list($context['tenant'], $filters);
        $this->json($response);
    }

    public function revoke(string $id): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        $body = $this->getJsonBody();
        try {
            $this->service->revoke($context['tenant'], (int) $id, $body['meta'] ?? null);
            $this->noContent();
        } catch (DomainException $e) {
            $this->json([
                'error' => $e->getMessage(),
            ], $this->statusFor($e));
        }
    }

    private function statusFor(DomainException $e): int
    {
        return match ($e->getMessage()) {
            'pixel_not_found_or_inactive' => 404,
            'invalid_pixel_token' => 401,
            'validation_error' => 422,
            'consent_not_found' => 404,
            'tenant_mismatch' => 403,
            default => 400,
        };
    }
}
