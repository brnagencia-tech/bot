<?php
namespace App\Services\Destinations;

use App\Models\Event;
use App\Models\Pixel;

class MetaCapiService
{
    private const GRAPH_URL = 'https://graph.facebook.com/v17.0';

    public function supports(Pixel $pixel): bool
    {
        $config = $this->config($pixel);
        return !empty($config['enabled']) && !empty($config['pixel_id']) && !empty($config['access_token']);
    }

    /**
     * @return array<string,mixed>
     */
    public function send(Event $event, Pixel $pixel): array
    {
        $config = $this->config($pixel);
        if (!$this->supports($pixel)) {
            return [
                'status' => 'skipped',
                'message' => 'Meta CAPI não configurado para este pixel.',
            ];
        }

        $payload = $this->buildPayload($event, $config);
        $url = sprintf('%s/%s/events?access_token=%s', self::GRAPH_URL, $config['pixel_id'], urlencode($config['access_token']));

        $response = $this->postJson($url, $payload);
        return $response;
    }

    /**
     * @return array<string,mixed>
     */
    private function buildPayload(Event $event, array $config): array
    {
        $eventTime = strtotime($event->event_time) ?: time();
        $eventData = [
            'event_name' => $event->event_name,
            'event_time' => $eventTime,
            'event_id' => $event->event_idempotency,
            'action_source' => 'website',
        ];

        $decodedContext = $event->context_json ? json_decode($event->context_json, true) : [];
        if (!empty($decodedContext['page_url'])) {
            $eventData['event_source_url'] = $decodedContext['page_url'];
        }
        if (!empty($decodedContext['action_source'])) {
            $eventData['action_source'] = $decodedContext['action_source'];
        }

        $decodedPayload = $event->payload_json ? json_decode($event->payload_json, true) : [];
        if (!empty($decodedPayload)) {
            $eventData['custom_data'] = $decodedPayload;
        }

        $eventData['user_data'] = $this->buildUserData($event, $decodedContext);

        $body = ['data' => [$eventData]];
        if (!empty($config['test_event_code'])) {
            $body['test_event_code'] = $config['test_event_code'];
        }
        return $body;
    }

    /**
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    private function buildUserData(Event $event, array $context): array
    {
        $userData = [];
        $userIds = $event->user_ids_json ? json_decode($event->user_ids_json, true) : [];
        if (!empty($userIds['advanced_matching']) && is_array($userIds['advanced_matching'])) {
            foreach ($userIds['advanced_matching'] as $key => $value) {
                $userData[$key] = $value;
            }
        }
        if (!empty($context['client_ip_address'])) {
            $userData['client_ip_address'] = $context['client_ip_address'];
        }
        if (!empty($context['client_user_agent'])) {
            $userData['client_user_agent'] = $context['client_user_agent'];
        }
        return $userData;
    }

    /**
     * @param array<string,mixed> $body
     * @return array<string,mixed>
     */
    private function postJson(string $url, array $body): array
    {
        $payload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $attempts = 0;
        $maxAttempts = 3;
        $responseBody = null;
        $httpCode = 0;
        $curlError = null;

        while ($attempts < $maxAttempts) {
            $attempts++;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if (!$curlError && $httpCode >= 200 && $httpCode < 300) {
                break;
            }

            // backoff exponencial simples
            usleep(100000 * $attempts);
        }

        if ($curlError) {
            return [
                'status' => 'error',
                'error' => $curlError,
                'http_status' => $httpCode,
                'attempts' => $attempts,
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'status' => 'success',
                'http_status' => $httpCode,
                'response' => $this->decodeJson($responseBody),
                'attempts' => $attempts,
            ];
        }

        return [
            'status' => 'error',
            'http_status' => $httpCode,
            'response' => $this->decodeJson($responseBody),
            'attempts' => $attempts,
        ];
    }

    private function decodeJson(?string $json): mixed
    {
        if ($json === null || $json === '') {
            return null;
        }
        $decoded = json_decode($json, true);
        return $decoded === null ? $json : $decoded;
    }

    /**
     * @return array<string,mixed>
     */
    private function config(Pixel $pixel): array
    {
        if (!$pixel->config_json) {
            return [];
        }
        $config = json_decode($pixel->config_json, true);
        if (!is_array($config)) {
            return [];
        }
        return $config['meta_capi'] ?? [];
    }
}
