<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for multi-store stock transfer workflow.
     */
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id('transfer_id');
            $table->string('transfer_no', 50)->unique();
            $table->unsignedBigInteger('from_branch_id')->default(1);
            $table->unsignedBigInteger('to_branch_id')->default(2);
            $table->enum('status', ['REQUESTED', 'APPROVED', 'PICKED', 'SHIPPED', 'RECEIVED', 'CANCELLED'])->default('REQUESTED');

            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('shipped_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['from_branch_id', 'to_branch_id', 'status'], 'idx_transfers_branch_status');
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id');
            $table->unsignedBigInteger('variant_id');
            $table->integer('quantity_requested');
            $table->integer('quantity_shipped')->default(0);
            $table->integer('quantity_received')->default(0);
            $table->timestamps();

            $table->foreign('transfer_id')->references('transfer_id')->on('stock_transfers')->onDelete('cascade');
            $table->index(['transfer_id', 'variant_id'], 'idx_transfer_items_trans_variant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }
};
