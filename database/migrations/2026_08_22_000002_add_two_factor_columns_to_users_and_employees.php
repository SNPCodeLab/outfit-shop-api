<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persistent two-factor authentication storage for both authenticatable
 * models. Secrets are stored encrypted (app key) via decrypt()/encrypt().
 * Cache-based storage was rejected: cache eviction would silently disable 2FA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable();
            }
            if (! Schema::hasColumn('users', 'two_factor_verified_at')) {
                $table->timestamp('two_factor_verified_at')->nullable();
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable();
            }
            if (! Schema::hasColumn('employees', 'two_factor_verified_at')) {
                $table->timestamp('two_factor_verified_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_verified_at']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_verified_at']);
        });
    }
};
