<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\WebhookSubscription;
use App\Services\WebhookDispatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookSubscriptionController extends BaseApiController
{
    /**
     * List all registered webhook subscriptions.
     */
    public function index(): JsonResponse
    {
        return $this->successResponse(WebhookSubscription::all(), 'Webhook subscriptions retrieved');
    }

    /**
     * Subscribe a new webhook URL to system events.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url|max:500',
            'event_type' => 'required|string|in:LOW_STOCK_ALERT,PO_RECEIVED,SHIFT_DISCREPANCY,REFUND_REQUESTED,STOCK_TRANSFER_COMPLETED,ALL',
            'secret' => 'nullable|string|max:100',
        ]);

        $subscription = WebhookSubscription::create([
            'url' => $validated['url'],
            'event_type' => $validated['event_type'],
            'secret' => $validated['secret'] ?? Str::random(32),
            'is_active' => true,
            'created_by' => $request->user()?->id ?? $request->user()?->employee_id,
        ]);

        return $this->createdResponse(
            $subscription,
            "Successfully subscribed to [{$subscription->event_type}] events"
        );
    }

    /**
     * Send a test ping webhook event to verify consumer connectivity.
     */
    public function test(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        WebhookDispatcherService::dispatch('PING_TEST', [
            'message' => 'Webhook test ping from CSMS / SS-MIS backend gateway',
            'timestamp' => now()->toISOString(),
            'status' => 'OK',
        ]);

        return $this->successResponse(null, 'Test webhook dispatched successfully');
    }

    /**
     * Delete/unsubscribe a webhook.
     */
    public function destroy(int $id): JsonResponse
    {
        $sub = WebhookSubscription::findOrFail($id);
        $sub->delete();

        return $this->successResponse(null, 'Webhook subscription removed successfully');
    }
}
