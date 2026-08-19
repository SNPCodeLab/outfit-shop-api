<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Brands Table ──────────────────────────────────────────────────
        Schema::create('brands', function (Blueprint $table) {
            $table->id('brand_id');
            $table->string('brand_name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->string('logo_url', 500)->nullable();
            $table->string('banner_url', 500)->nullable();
            $table->string('country_of_origin', 50)->default('Cambodia');
            $table->text('description')->nullable();
            $table->string('website_url', 255)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('is_featured', 'idx_brands_featured');
            $table->index('slug', 'idx_brands_slug');
        });

        // Add brand_id foreign key to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands', 'brand_id')->nullOnDelete();
        });

        // ── 2. Store Branches & Multi-Location Inventory ─────────────────────
        Schema::create('store_branches', function (Blueprint $table) {
            $table->id('branch_id');
            $table->string('branch_name', 100);
            $table->string('branch_code', 30)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 50)->default('Phnom Penh');
            $table->boolean('is_warehouse')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('is_active', 'idx_branches_active');
            $table->index('branch_code', 'idx_branches_code');
        });

        Schema::create('store_inventories', function (Blueprint $table) {
            $table->id('inventory_id');
            $table->foreignId('branch_id')->constrained('store_branches', 'branch_id')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants', 'variant_id')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reorder_level')->default(5);
            $table->timestamps();

            // Unique composite index: 1 stock record per variant per branch
            $table->unique(['branch_id', 'variant_id'], 'uk_branch_variant');
        });

        // ── 3. Promotions & Coupon Vouchers ──────────────────────────────────
        Schema::create('promotions', function (Blueprint $table) {
            $table->id('promotion_id');
            $table->string('title', 150);
            $table->string('promo_code', 50)->nullable()->unique();
            $table->string('discount_type', 30)->default('PERCENTAGE'); // PERCENTAGE, FIXED_AMOUNT, BUY_X_GET_Y
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_spend', 10, 2)->default(0.00);
            $table->string('target_department', 50)->nullable(); // APPAREL, BEVERAGE, BOOKS_MEDIA, ALL
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('max_usage_count')->nullable();
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['is_active', 'start_date', 'end_date'], 'idx_promo_active_dates');
        });

        // ── 4. Product Bundles & Combo Packs ─────────────────────────────────
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id('bundle_id');
            $table->string('bundle_name', 150);
            $table->string('sku', 50)->unique();
            $table->string('barcode', 50)->nullable()->unique();
            $table->decimal('bundle_price', 12, 2);
            $table->decimal('original_total_price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id('bundle_item_id');
            $table->foreignId('bundle_id')->constrained('product_bundles', 'bundle_id')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants', 'variant_id')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // ── 5. Product Reviews & Ratings ─────────────────────────────────────
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->foreignId('product_id')->constrained('products', 'product_id')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers', 'customer_id')->nullOnDelete();
            $table->string('reviewer_name', 100);
            $table->integer('rating')->default(5); // 1 to 5 stars
            $table->string('title', 150)->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['product_id', 'is_approved', 'rating'], 'idx_reviews_product_rating');
        });

        // ── 6. Customer Wishlists & Saved Items ──────────────────────────────
        Schema::create('customer_wishlists', function (Blueprint $table) {
            $table->id('wishlist_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products', 'product_id')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants', 'variant_id')->nullOnDelete();
            $table->timestamps();

            // Prevent duplicate wishlist entry for same customer & product
            $table->unique(['customer_id', 'product_id'], 'uk_customer_product_wishlist');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_wishlists');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('bundle_items');
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('store_inventories');
        Schema::dropIfExists('store_branches');

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });

        Schema::dropIfExists('brands');
    }
};
