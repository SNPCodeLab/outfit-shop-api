<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Standardize the identity tables into a complete, uniform user profile:
 * every account in the system (admin, manager, cashier, staff) is recorded
 * with contact details, profile picture, join date, lifecycle status, and
 * login telemetry. Also adds the missing users.employee_id relation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 100)->nullable()->after('name');
                $table->unique('username', 'uq_users_username');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url', 500)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'joined_at')) {
                $table->date('joined_at')->nullable()->after('avatar_url');
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('ACTIVE')->after('joined_at');
                $table->index(['status'], 'idx_users_status');
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('users', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('is_admin');
            }
        });

        // Link system accounts to their employee record when both exist.
        $orphanUserIds = DB::table('users')
            ->whereNotNull('employee_id')
            ->whereNotIn('employee_id', DB::table('employees')->select('employee_id'))
            ->pluck('id');
        if ($orphanUserIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $orphanUserIds)->update(['employee_id' => null]);
        }

        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getForeignKeys('users');
            $hasEmployeeFk = collect($sm)->contains(fn ($fk) => in_array('employee_id', $fk['columns'] ?? []));
            if (! $hasEmployeeFk) {
                $table->foreign('employee_id')
                    ->references('employee_id')
                    ->on('employees')
                    ->nullOnDelete();
            }
        });

        // Login telemetry parity for employee accounts (the primary POS identity)
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            if (! Schema::hasColumn('employees', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable();
            }
            if (! Schema::hasColumn('employees', 'joined_at')) {
                $table->date('joined_at')->nullable();
            }
            if (! Schema::hasColumn('employees', 'avatar_url')) {
                $table->string('avatar_url', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getForeignKeys('users');
            if (collect($sm)->contains(fn ($fk) => in_array('employee_id', $fk['columns'] ?? []))) {
                $table->dropForeign(['employee_id']);
            }
            $table->dropColumn([
                'username', 'phone', 'avatar_url', 'joined_at', 'status',
                'last_login_at', 'last_login_ip', 'deleted_at', 'employee_id',
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'last_login_ip', 'joined_at', 'avatar_url']);
        });
    }
};
