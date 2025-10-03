<?php
namespace App\Controllers\Api;

use App\Exceptions\SchemaValidationException;
use App\Services\IngestService;
use DomainException;

class IngestController extends Controller
{
    private IngestService $service;

    public function __construct()
    {
        $this->service = new IngestService();
    }

    public function ingest(): void
    {
        $body = $this->getJsonBody();
        $this->assertRequired($body, ['pixel_id', 'event_name']);

        $pixelId = $body['pixel_id'];
        $token = $_SERVER['HTTP_X_BRN_PIXEL_TOKEN'] ?? ($body['pixel_token'] ?? null);
        if (!is_string($token) || trim($token) === '') {
            $this->json([
                'error' => 'unauthorized',
                'message' => 'X-BRN-Pixel-Token header obrigatório.'
            ], 401);
        }

        try {
            $result = $this->service->ingest(
                $body,
                (string) $pixelId,
                trim((string) $token),
                $this->requestContext()
            );
            $this->json([
                'event_id' => $result['event_id'],
                'event_idempotency' => $result['event_idempotency'],
                'deduplicated' => $result['deduplicated'],
            ], $result['deduplicated'] ? 200 : 202);
        } catch (SchemaValidationException $e) {
            $this->json([
                'error' => $e->getMessage(),
                'missing_fields' => $e->missing(),
            ], 422);
        } catch (DomainException $e) {
            $this->handleDomain($e);
        }
    }

    private function handleDomain(DomainException $e): void
    {
        $code = $e->getMessage();
        $status = match ($code) {
            'unauthorized', 'invalid_pixel_token' => 401,
            'pixel_not_found_or_inactive' => 404,
            'event_name_required', 'event_id_required' => 422,
            default => 400,
        };
        $this->json([
            'error' => $code,
        ], $status);
    }
}
