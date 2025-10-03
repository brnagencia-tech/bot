<?php
namespace App\Controllers\Api;

use App\Services\AuthService;
use DomainException;

class AuthController extends Controller
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function register(): void
    {
        $body = $this->getJsonBody();
        $this->assertRequired($body, ['email', 'password']);
        if (empty($body['tenant_id']) && empty($body['tenant_name'])) {
            $this->json([
                'error' => 'validation_error',
                'message' => 'Informe tenant_id ou tenant_name.',
            ], 422);
        }

        try {
            $result = $this->service->register($body);
            $this->json([
                'token' => $result['token'],
                'user' => $result['user'],
                'tenant' => $result['tenant'],
            ], 201);
        } catch (DomainException $e) {
            $this->handleDomainException($e);
        }
    }

    public function login(): void
    {
        $body = $this->getJsonBody();
        $this->assertRequired($body, ['email', 'password']);
        try {
            $result = $this->service->login($body['email'], $body['password']);
            $this->json($result, 200);
        } catch (DomainException $e) {
            $this->handleDomainException($e);
        }
    }

    public function logout(): void
    {
        $token = $this->bearerToken();
        if (!$token) {
            $this->json([
                'error' => 'unauthorized',
                'message' => 'Token não informado.'
            ], 401);
        }

        $user = $this->service->findByToken($token);
        if (!$user) {
            $this->json([
                'error' => 'unauthorized',
                'message' => 'Token inválido ou expirado.'
            ], 401);
        }

        $this->service->logout($user);
        $this->noContent();
    }

    private function handleDomainException(DomainException $e): void
    {
        $code = $e->getMessage();
        $status = $this->statusFor($code);
        $this->json([
            'error' => $code,
        ], $status);
    }

    private function statusFor(string $code): int
    {
        return match ($code) {
            'email_already_registered', 'tenant_name_in_use' => 409,
            'tenant_not_found' => 404,
            'tenant_name_required' => 422,
            'invalid_credentials' => 401,
            'user_without_tenant' => 422,
            default => 400,
        };
    }
}
