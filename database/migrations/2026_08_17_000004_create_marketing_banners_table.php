<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_banners', function (Blueprint $table) {
            $table->id('banner_id');
            $table->string('title', 150);
            $table->string('subtitle', 255)->nullable();
            $table->string('image_url', 500);
            $table->string('image_public_id', 255)->nullable();
            $table->string('link_url', 500)->nullable();
            $table->string('placement', 50)->default('HERO_SLIDER'); // HERO_SLIDER, PROMO_CARD, SECTION_BANNER, POPUP
            $table->string('target_department', 50)->nullable(); // APPAREL, BEVERAGE, BOOKS_MEDIA, ALL
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['placement', 'is_active', 'sort_order'], 'idx_banners_placement_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_banners');
    }
};
