<?php
namespace App\Services;

use App\Exceptions\SchemaValidationException;
use App\Models\Event;
use App\Models\EventSchema;
use App\Models\Pixel;
use App\Models\PixelToken;
use App\Support\AdvancedMatching;
use App\Services\EventDispatcher;
use DomainException;

class IngestService
{
    private EventDispatcher $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $requestContext
     * @return array{event_id:int,event_idempotency:string,deduplicated:bool}
     */
    public function ingest(array $payload, string $pixelPublicId, string $token, array $requestContext): array
    {
        $pixel = Pixel::findByPublicId($pixelPublicId);
        if (!$pixel || !$pixel->isActive()) {
            throw new DomainException('pixel_not_found_or_inactive');
        }

        if (!PixelToken::verify($pixel->id, $token)) {
            throw new DomainException('invalid_pixel_token');
        }

        $eventName = $this->stringValue($payload['event_name'] ?? null);
        if ($eventName === null) {
            throw new DomainException('event_name_required');
        }

        $eventIdempotency = $this->stringValue($payload['event_id'] ?? $payload['event_idempotency'] ?? null);
        if ($eventIdempotency === null) {
            throw new DomainException('event_id_required');
        }

        $eventTime = $this->resolveTime($payload['event_time'] ?? null);

        $existing = Event::findByIdempotency($pixel->tenant_id, $eventIdempotency);
        if ($existing) {
            return [
                'event_id' => $existing->id,
                'event_idempotency' => $existing->event_idempotency,
                'deduplicated' => true,
            ];
        }

        $schema = EventSchema::findActive($pixel->tenant_id, $eventName);
        if ($schema) {
            $this->validateAgainstSchema($schema->decoded(), $payload['payload'] ?? []);
        }

        $advancedMatching = AdvancedMatching::normalize($payload['advanced_matching'] ?? []);

        $userIds = [];
        if ($advancedMatching) {
            $userIds['advanced_matching'] = $advancedMatching;
        }
        if (isset($payload['user_id'])) {
            $userIds['user_id'] = (string) $payload['user_id'];
        }
        if (isset($payload['anonymous_id'])) {
            $userIds['anonymous_id'] = (string) $payload['anonymous_id'];
        }
        if (isset($payload['session_id'])) {
            $userIds['session_id'] = (string) $payload['session_id'];
        }

        $context = $this->buildContext($payload['context'] ?? [], $requestContext);

        $event = Event::create([
            'tenant_id' => $pixel->tenant_id,
            'pixel_id' => $pixel->id,
            'event_name' => $eventName,
            'event_time' => $eventTime,
            'event_idempotency' => $eventIdempotency,
            'user_ids' => $userIds ?: null,
            'payload' => $payload['payload'] ?? [],
            'context' => $context,
        ]);

        $this->dispatcher->dispatch($event);

        return [
            'event_id' => $event->id,
            'event_idempotency' => $event->event_idempotency,
            'deduplicated' => false,
        ];
    }

    /**
     * @param array<string,mixed> $schema
     * @param array<string,mixed> $payload
     */
    private function validateAgainstSchema(array $schema, array $payload): void
    {
        $required = isset($schema['required']) && is_array($schema['required']) ? $schema['required'] : [];
        $missing = [];
        foreach ($required as $field) {
            if (!array_key_exists($field, $payload) || $payload[$field] === null || $payload[$field] === '') {
                $missing[] = $field;
            }
        }
        if ($missing) {
            throw new SchemaValidationException($missing);
        }
    }

    /**
     * @param array<string,mixed> $context
     * @param array<string,mixed> $requestContext
     * @return array<string,mixed>
     */
    private function buildContext(array $context, array $requestContext): array
    {
        $result = $context;
        foreach ($requestContext as $key => $value) {
            if (!isset($result[$key]) && $value !== null) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    private function resolveTime(mixed $value): string
    {
        if (is_numeric($value)) {
            return gmdate('Y-m-d H:i:s', (int) $value);
        }
        if (is_string($value) && strtotime($value) !== false) {
            return gmdate('Y-m-d H:i:s', strtotime($value));
        }
        return gmdate('Y-m-d H:i:s');
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            $value = trim($value);
            return $value !== '' ? $value : null;
        }
        if (is_numeric($value)) {
            return (string) $value;
        }
        return null;
    }
}
