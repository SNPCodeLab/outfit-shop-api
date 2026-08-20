<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colors', function (Blueprint $table) {
            if (! Schema::hasColumn('colors', 'hex_code')) {
                $table->string('hex_code', 20)->nullable()->after('color_name');
            }
        });

        Schema::table('clothing_sizes', function (Blueprint $table) {
            if (! Schema::hasColumn('clothing_sizes', 'size_code')) {
                $table->string('size_code', 20)->nullable()->after('size_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clothing_sizes', function (Blueprint $table) {
            $table->dropColumn('size_code');
        });

        Schema::table('colors', function (Blueprint $table) {
            $table->dropColumn('hex_code');
        });
    }
};
