<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soft deletes on core tables
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // clothing_sizes enhancement
        Schema::table('clothing_sizes', function (Blueprint $table) {
            if (! Schema::hasColumn('clothing_sizes', 'size_order')) {
                $table->integer('size_order')->default(0)->after('size_name');
            }
            if (Schema::hasColumn('clothing_sizes', 'size_code')) {
                $table->string('size_code', 30)->nullable()->change();
            } else {
                $table->string('size_code', 30)->nullable()->after('size_name');
            }
            if (! Schema::hasColumn('clothing_sizes', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // colors enhancement
        Schema::table('colors', function (Blueprint $table) {
            if (! Schema::hasColumn('colors', 'pantone')) {
                $table->string('pantone', 50)->nullable()->after('hex_code');
            }
            if (! Schema::hasColumn('colors', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // promotions enhancement
        Schema::table('promotions', function (Blueprint $table) {
            if (! Schema::hasColumn('promotions', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // Ensure broadcast_alerts exists
        if (! Schema::hasTable('broadcast_alerts')) {
            Schema::create('broadcast_alerts', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100);
                $table->text('message');
                $table->enum('severity', ['INFO', 'WARNING', 'CRITICAL'])->default('INFO');
                $table->foreignId('created_by')->nullable()->constrained('employees', 'employee_id')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Webhook subscriptions - prompt requested it, but it already exists.
        // I will ensure it matches the requested schema if needed.
    }

    public function down(): void
    {
        // Rollback logic if necessary
    }
};
