<?php
namespace App\Controllers\Api;

use App\Services\WebhookService;
use DomainException;

class WebhooksController extends Controller
{
    private WebhookService $service;

    public function __construct()
    {
        $this->service = new WebhookService();
    }

    public function index(): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        $records = $this->service->list($context['tenant']);
        $this->json(['data' => $records]);
    }

    public function store(): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        $body = $this->getJsonBody();
        try {
            $webhook = $this->service->create($context['tenant'], $body);
            $this->json($webhook, 201);
        } catch (DomainException $e) {
            $this->json(['error' => $e->getMessage()], $this->statusFor($e));
        }
    }

    public function update(string $id): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        $body = $this->getJsonBody();
        try {
            $webhook = $this->service->update($context['tenant'], (int) $id, $body);
            $this->json($webhook);
        } catch (DomainException $e) {
            $this->json(['error' => $e->getMessage()], $this->statusFor($e));
        }
    }

    public function destroy(string $id): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        try {
            $this->service->delete($context['tenant'], (int) $id);
            $this->noContent();
        } catch (DomainException $e) {
            $this->json(['error' => $e->getMessage()], $this->statusFor($e));
        }
    }

    public function rotate(string $id): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        try {
            $webhook = $this->service->rotateSecret($context['tenant'], (int) $id);
            $this->json($webhook);
        } catch (DomainException $e) {
            $this->json(['error' => $e->getMessage()], $this->statusFor($e));
        }
    }

    public function run(): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        // Apenas usuários master podem disparar manualmente
        if ($context['role'] !== 'master') {
            $this->json(['error' => 'forbidden'], 403);
        }
        $results = $this->service->runPendingDeliveries();
        $this->json(['processed' => count($results)]);
    }

    private function statusFor(DomainException $e): int
    {
        return match ($e->getMessage()) {
            'validation_error' => 422,
            'webhook_not_found' => 404,
            'tenant_mismatch' => 403,
            default => 400,
        };
    }
}
