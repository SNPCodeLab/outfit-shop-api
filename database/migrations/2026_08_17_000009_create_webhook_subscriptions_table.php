<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for webhook subscriptions table.
     */
    public function up(): void
    {
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500);
            $table->string('event_type', 100); // LOW_STOCK_ALERT, PO_RECEIVED, SHIFT_DISCREPANCY, REFUND_REQUESTED, STOCK_TRANSFER_COMPLETED, ALL
            $table->string('secret', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'is_active'], 'idx_webhooks_event_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_subscriptions');
    }
};
