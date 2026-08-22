<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce idempotent checkout at the database level.
 *
 * The application-level check-then-act guard in POSService loses the race
 * under concurrent retries with the same Idempotency-Key. A UNIQUE index
 * makes the guarantee atomic: the second insert fails and the winner's
 * sale is returned instead of creating a duplicate charge.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Null out duplicate keys (keeping the earliest sale per key) so the
        // unique index can be applied against historical data. Nulling only
        // disables idempotent replay for those duplicates; no sale data changes.
        // Postgres does not allow SELECT aliases inside HAVING, so the
        // aggregate is repeated verbatim (sqlite/MySQL accept it either way).
        $duplicateKeys = DB::table('sale_headers')
            ->whereNotNull('idempotency_key')
            ->select('idempotency_key')
            ->groupBy('idempotency_key')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('idempotency_key');

        foreach ($duplicateKeys as $key) {
            $keepId = DB::table('sale_headers')
                ->where('idempotency_key', $key)
                ->min('sale_id');

            DB::table('sale_headers')
                ->where('idempotency_key', $key)
                ->where('sale_id', '!=', $keepId)
                ->update(['idempotency_key' => null]);
        }

        Schema::table('sale_headers', function (Blueprint $table) {
            $table->unique('idempotency_key', 'uq_sale_headers_idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('sale_headers', function (Blueprint $table) {
            $table->dropUnique('uq_sale_headers_idempotency_key');
        });
    }
};
