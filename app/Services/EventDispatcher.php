<?php
namespace App\Services;

use App\Models\Event;
use App\Models\Pixel;
use App\Services\Destinations\MetaCapiService;
use App\Services\WebhookService;

class EventDispatcher
{
    private MetaCapiService $metaService;
    private WebhookService $webhookService;

    public function __construct()
    {
        $this->metaService = new MetaCapiService();
        $this->webhookService = new WebhookService();
    }

    /**
     * @param array<int,Event> $events
     */
    public function dispatchMany(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }

    public function dispatch(Event $event): void
    {
        $pixel = $event->pixel_id ? Pixel::findById($event->pixel_id) : null;
        if (!$pixel) {
            $event->markStatus('dropped', [
                'reason' => 'pixel_missing',
                'message' => 'Evento sem pixel associado ou pixel removido.',
            ]);
            return;
        }

        $event->markStatus('processing', [
            'destination' => 'meta_capi',
            'message' => 'Processando envio (stub).',
        ]);

        $metaPayload = null;
        $metaStatus = 'skipped';
        if ($this->metaService->supports($pixel)) {
            $metaPayload = $this->metaService->send($event, $pixel);
            $metaStatus = $metaPayload['status'] ?? 'unknown';
        }

        $webhookCount = $this->webhookService->enqueueDeliveries($pixel->tenant_id, $event->id);

        $status = $metaStatus === 'error' ? 'failed' : 'delivered';
        $event->markStatus($status, [
            'destinations' => array_values(array_filter([$metaPayload])),
            'webhooks' => ['queued' => $webhookCount],
        ]);
    }
}
