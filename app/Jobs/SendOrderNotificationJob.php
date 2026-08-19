<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SaleHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $saleId,
        public string $channel = 'EMAIL'
    ) {}

    /**
     * Send email or SMS electronic receipt asynchronously.
     */
    public function handle(): void
    {
        $sale = SaleHeader::with(['customer', 'payments'])->find($this->saleId);
        if (! $sale || ! $sale->customer) {
            return;
        }

        $recipient = $sale->customer->email ?? $sale->customer->phone;
        Log::info("Async dispatching {$this->channel} receipt for Invoice [{$sale->invoice_no}] to {$recipient}");

        // Email / SMS provider dispatch (e.g. Mail::send or Twilio SMS)
    }
}
