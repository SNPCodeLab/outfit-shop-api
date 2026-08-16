<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. POS Cash Drawer Shifts & Z-Reports ─────────────────────────────
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id('shift_id');
            $table->foreignId('employee_id')->constrained('employees', 'employee_id');
            $table->foreignId('branch_id')->nullable()->constrained('store_branches', 'branch_id')->nullOnDelete();
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_float_usd', 12, 2)->default(0.00);
            $table->decimal('opening_float_khr', 14, 2)->default(0.00);
            $table->decimal('cash_sales_usd', 12, 2)->default(0.00);
            $table->decimal('cash_sales_khr', 14, 2)->default(0.00);
            $table->decimal('card_sales_usd', 12, 2)->default(0.00);
            $table->decimal('qr_sales_usd', 12, 2)->default(0.00);
            $table->decimal('petty_cash_drops_usd', 12, 2)->default(0.00);
            $table->decimal('expected_cash_usd', 12, 2)->default(0.00);
            $table->decimal('closing_cash_usd', 12, 2)->nullable();
            $table->decimal('discrepancy_usd', 12, 2)->nullable();
            $table->string('status', 20)->default('OPEN'); // OPEN, CLOSED
            $table->text('notes')->nullable();
            $table->jsonb('z_report_summary')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status'], 'idx_shifts_emp_status');
        });

        // ── 2. VIP Loyalty Tiers & Points Ledger ─────────────────────────────
        Schema::table('customers', function (Blueprint $table) {
            $table->string('vip_tier', 30)->default('BRONZE')->after('loyalty_points'); // BRONZE, SILVER, GOLD, PLATINUM
            $table->decimal('total_spent_lifetime', 14, 2)->default(0.00)->after('vip_tier');
            $table->decimal('store_credit_balance', 12, 2)->default(0.00)->after('total_spent_lifetime');
        });

        Schema::create('customer_loyalty_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id')->cascadeOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sale_headers', 'sale_id')->nullOnDelete();
            $table->string('transaction_type', 30); // EARN, REDEEM, ADJUSTMENT, BONUS
            $table->integer('points');
            $table->integer('balance_after');
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index('customer_id', 'idx_loyalty_customer');
        });

        // ── 3. Volume-Tiered Wholesale Pricing ───────────────────────────────
        Schema::create('variant_pricing_tiers', function (Blueprint $table) {
            $table->id('tier_id');
            $table->foreignId('variant_id')->constrained('product_variants', 'variant_id')->cascadeOnDelete();
            $table->integer('min_quantity');
            $table->integer('max_quantity')->nullable(); // NULL means min_quantity and above
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->timestamps();

            $table->index('variant_id', 'idx_pricing_tiers_variant');
        });

        // ── 4. Digital Gift Cards & Vouchers ─────────────────────────────────
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id('card_id');
            $table->string('card_code', 50)->unique();
            $table->string('pin_hash', 255)->nullable();
            $table->decimal('initial_balance', 12, 2);
            $table->decimal('current_balance', 12, 2);
            $table->foreignId('purchaser_customer_id')->nullable()->constrained('customers', 'customer_id')->nullOnDelete();
            $table->dateTime('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 5. Omnichannel Shipping, Delivery & Click-and-Collect ─────────────
        Schema::create('shipping_orders', function (Blueprint $table) {
            $table->id('shipping_id');
            $table->foreignId('sale_id')->constrained('sale_headers', 'sale_id')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('store_branches', 'branch_id')->nullOnDelete();
            $table->string('fulfillment_type', 40)->default('IN_STORE_PICKUP'); // IN_STORE_PICKUP, COURIER_DELIVERY
            $table->string('courier_name', 50)->nullable(); // VIRAK_BUNTHAM, J_AND_T, GRAB_EXPRESS, OWN_DELIVERY
            $table->string('tracking_number', 100)->nullable();
            $table->string('recipient_name', 100);
            $table->string('recipient_phone', 30);
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city', 50)->default('Phnom Penh');
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->string('status', 30)->default('PENDING'); // PENDING, PACKED, DISPATCHED, DELIVERED, RETURNED
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'fulfillment_type'], 'idx_shipping_status_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_orders');
        Schema::dropIfExists('gift_cards');
        Schema::dropIfExists('variant_pricing_tiers');
        Schema::dropIfExists('customer_loyalty_logs');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['vip_tier', 'total_spent_lifetime', 'store_credit_balance']);
        });

        Schema::dropIfExists('pos_shifts');
    }
};
