<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class WebhookValidator
{
    public static function ensureValid(Request $request, string $configKey = 'services.pix.shared_secret'): void
    {
        if (! $request->isSecure() && ! app()->environment('local')) {
            throw new AccessDeniedHttpException('HTTPS is required for webhook calls.');
        }

        $expected = (string) config($configKey);

        if ($expected === '') {
            return;
        }

        $provided = (string) $request->header('X-Webhook-Token');

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            throw new AccessDeniedHttpException('Invalid webhook signature.');
        }
    }
}
