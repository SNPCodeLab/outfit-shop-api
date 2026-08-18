<?php

namespace App\Services;

use App\Models\WebhookSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookDispatcherService
{
    /**
     * Dispatch an event to all active subscribed webhook endpoints.
     *
     * @param  string  $eventType  LOW_STOCK_ALERT | PO_RECEIVED | SHIFT_DISCREPANCY | REFUND_REQUESTED | STOCK_TRANSFER_COMPLETED
     */
    public static function dispatch(string $eventType, array $payload): void
    {
        $subscriptions = WebhookSubscription::where('is_active', true)
            ->where(function ($q) use ($eventType) {
                $q->where('event_type', $eventType)
                    ->orWhere('event_type', 'ALL');
            })
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $body = [
            'event' => $eventType,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
            'event_id' => (string) Str::uuid(),
        ];

        $jsonPayload = json_encode($body);

        foreach ($subscriptions as $sub) {
            try {
                $secret = $sub->secret ?? config('app.key');
                $signature = hash_hmac('sha256', $jsonPayload, $secret);

                Http::timeout(5)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'User-Agent' => 'CSMS-Webhook-Dispatcher/1.0',
                        'X-Webhook-Event' => $eventType,
                        'X-Webhook-Signature' => "sha256={$signature}",
                    ])
                    ->post($sub->url, $body);

                Log::info("Webhook [{$eventType}] dispatched successfully to: {$sub->url}");
            } catch (\Throwable $e) {
                Log::warning("Failed to dispatch webhook [{$eventType}] to {$sub->url}: ".$e->getMessage());
            }
        }
    }
}
