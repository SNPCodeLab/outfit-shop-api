<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (! Schema::hasColumn('brands', 'logo_url')) {
                    $table->string('logo_url', 2048)->nullable()->after('brand_name');
                } else {
                    $table->string('logo_url', 2048)->nullable()->change();
                }

                if (Schema::hasColumn('brands', 'banner_url')) {
                    $table->string('banner_url', 2048)->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (Schema::hasColumn('brands', 'logo_url')) {
                    $table->string('logo_url', 500)->nullable()->change();
                }
                if (Schema::hasColumn('brands', 'banner_url')) {
                    $table->string('banner_url', 500)->nullable()->change();
                }
            });
        }
    }
};
