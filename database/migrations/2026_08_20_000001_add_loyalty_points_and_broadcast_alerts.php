<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Add missing loyalty_points column to customers ─────────────────
        // Migration 000006 tried to add vip_tier AFTER loyalty_points, but the
        // column was never created. Add it now with a safe guard.
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'loyalty_points')) {
                $table->integer('loyalty_points')->default(0);
            }
        });

        // ── 2. Create system_broadcast_alerts table ───────────────────────────
        // Used by GET /alerts/active, GET /admin/master-pulse, and
        // POST /admin/broadcast-alert. Missing table caused 500 on all three.
        if (! Schema::hasTable('system_broadcast_alerts')) {
            Schema::create('system_broadcast_alerts', function (Blueprint $table) {
                $table->id('alert_id');
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->string('title', 255);
                $table->text('message');
                $table->string('priority', 20)->default('HIGH'); // LOW, NORMAL, HIGH, URGENT
                $table->string('target_role', 20)->default('ALL'); // ALL, CASHIER, STAFF, MANAGER, ADMIN
                $table->boolean('is_active')->default(true);
                $table->dateTime('expires_at')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'alert_id'], 'idx_alerts_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_broadcast_alerts');

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'loyalty_points')) {
                $table->dropColumn('loyalty_points');
            }
        });
    }
};
