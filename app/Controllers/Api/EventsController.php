<?php
namespace App\Controllers\Api;

use App\Services\EventService;
use DomainException;

class EventsController extends Controller
{
    private EventService $service;

    public function __construct()
    {
        $this->service = new EventService();
    }

    public function index(): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user);
        $filters = $this->filters();
        $result = $this->service->list($context['tenant'], $filters);
        $this->json($result);
    }

    public function show(string $id): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user);
        try {
            $event = $this->service->show($context['tenant'], (int) $id);
            $this->json($event);
        } catch (DomainException $e) {
            $this->json([
                'error' => $e->getMessage(),
            ], $this->statusFor($e));
        }
    }

    public function metrics(): void
    {
        $user = $this->requireUser();
        $context = $this->requireTenant($user);
        $filters = $this->filters();
        $metrics = $this->service->metrics($context['tenant'], $filters);
        $this->json($metrics);
    }

    /**
     * @return array<string,mixed>
     */
    private function filters(): array
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'event_name' => $_GET['event_name'] ?? null,
            'pixel_id' => $_GET['pixel_id'] ?? null,
            'pixel_public_id' => $_GET['pixel_public_id'] ?? null,
            'from' => $_GET['from'] ?? null,
            'to' => $_GET['to'] ?? null,
            'search' => $_GET['search'] ?? null,
            'page' => $_GET['page'] ?? null,
            'per_page' => $_GET['per_page'] ?? null,
        ];
        return array_filter($filters, static function ($value) {
            return $value !== null && $value !== '';
        });
    }

    private function statusFor(DomainException $e): int
    {
        return match ($e->getMessage()) {
            'event_not_found' => 404,
            default => 400,
        };
    }
}
