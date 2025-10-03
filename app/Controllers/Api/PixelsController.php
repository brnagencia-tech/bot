<?php
namespace App\Controllers\Api;

use App\Services\PixelService;
use DomainException;

class PixelsController extends Controller
{
    private PixelService $service;

    public function __construct()
    {
        $this->service = new PixelService();
    }

    public function index(): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user);
        $pixels = $this->service->list($context['tenant']);
        $this->json(['data' => $pixels]);
    }

    public function store(): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        $body = $this->getJsonBody();
        try {
            $pixel = $this->service->create($context['tenant'], $body);
            $this->json($pixel, 201);
        } catch (DomainException $e) {
            $this->handleDomain($e);
        }
    }

    public function show(string $id): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user);
        try {
            $pixel = $this->service->show($context['tenant'], (int) $id);
            $this->json($pixel);
        } catch (DomainException $e) {
            $this->handleDomain($e);
        }
    }

    public function update(string $id): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        $body = $this->getJsonBody();
        try {
            $pixel = $this->service->update($context['tenant'], (int) $id, $body);
            $this->json($pixel);
        } catch (DomainException $e) {
            $this->handleDomain($e);
        }
    }

    public function deactivate(string $id): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        try {
            $this->service->deactivate($context['tenant'], (int) $id);
            $this->noContent();
        } catch (DomainException $e) {
            $this->handleDomain($e);
        }
    }

    public function issueToken(string $id): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        try {
            $token = $this->service->issueToken($context['tenant'], (int) $id);
            $this->json($token, 201);
        } catch (DomainException $e) {
            $this->handleDomain($e);
        }
    }

    public function revokeToken(string $id, string $tokenId): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user, true);
        try {
            $this->service->revokeToken($context['tenant'], (int) $id, (int) $tokenId);
            $this->noContent();
        } catch (DomainException $e) {
            $this->handleDomain($e);
        }
    }

    private function handleDomain(DomainException $e): void
    {
        $code = $e->getMessage();
        $status = match ($code) {
            'validation_error' => 422,
            'pixel_id_in_use' => 409,
            'pixel_not_found' => 404,
            'tenant_mismatch' => 403,
            'token_not_found' => 404,
            default => 400,
        };
        $this->json([
            'error' => $code,
        ], $status);
    }
}
