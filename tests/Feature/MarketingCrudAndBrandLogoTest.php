<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MarketingCrudAndBrandLogoTest extends TestCase
{
    use RefreshDatabase;

    private function makeEmployee(string $role): Employee
    {
        return Employee::create([
            'employee_name' => ucfirst(strtolower($role)).' User',
            'email' => strtolower($role).uniqid().'@test.local',
            'username' => strtolower($role).uniqid(),
            'password_hash' => Hash::make('Secret123'),
            'role' => $role,
            'status' => 'ACTIVE',
        ]);
    }

    private function token(Employee $employee): string
    {
        return 'Bearer '.$employee->createToken('test-token')->plainTextToken;
    }

    private function createVariant(): ProductVariant
    {
        $category = Category::create([
            'category_name' => 'T-Shirts '.uniqid(),
            'slug' => 't-shirts-'.uniqid(),
            'gender' => 'MEN',
        ]);

        $size = ClothingSize::create([
            'size_code' => 'M'.uniqid(),
            'size_name' => 'Medium '.uniqid(),
            'sort_order' => 1,
        ]);

        $color = Color::create([
            'color_name' => 'Navy '.uniqid(),
            'color_code' => '#000080',
        ]);

        $product = Product::create([
            'product_name' => 'Classic Tee '.uniqid(),
            'category_id' => $category->category_id,
            'base_price' => 25.00,
        ]);

        return ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id' => $size->size_id,
            'color_id' => $color->color_id,
            'sku' => 'SKU-'.uniqid(),
            'price' => 25.00,
            'cost_price' => 10.00,
            'stock_quantity' => 50,
        ]);
    }

    public function test_brand_logo_support_and_crud(): void
    {
        $admin = $this->makeEmployee('ADMIN');
        $longLogoUrl = 'https://res.cloudinary.com/test-cloud/image/upload/v1786898754/c_scale,w_800,f_auto,q_auto/special-brands/khmer-luxury-heritage-signature-embroidery-crest-official-high-resolution-brand-logo-asset-2026.png';

        $response = $this->withHeader('Authorization', $this->token($admin))
            ->postJson('/api/v1/brands', [
                'brand_name' => 'Khmer Heritage Brand',
                'logo_url' => $longLogoUrl,
                'banner_url' => 'https://res.cloudinary.com/test-cloud/image/upload/banner.png',
                'country_of_origin' => 'Cambodia',
                'description' => 'Fine luxury clothing brand.',
                'website_url' => 'https://khmerheritage.com',
                'is_featured' => true,
            ]);

        $response->assertStatus(201);
        $brandId = $response->json('data.brand_id');

        $this->assertDatabaseHas('brands', [
            'brand_id' => $brandId,
            'brand_name' => 'Khmer Heritage Brand',
            'logo_url' => $longLogoUrl,
        ]);

        $newLogoUrl = 'https://res.cloudinary.com/test-cloud/image/upload/v1786899999/updated-logo.png';
        $updateResponse = $this->withHeader('Authorization', $this->token($admin))
            ->putJson("/api/v1/brands/{$brandId}", [
                'logo_url' => $newLogoUrl,
                'description' => 'Updated luxury description.',
            ]);

        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('brands', [
            'brand_id' => $brandId,
            'logo_url' => $newLogoUrl,
            'description' => 'Updated luxury description.',
        ]);

        $getResponse = $this->getJson("/api/v1/brands/{$brandId}");
        $getResponse->assertStatus(200);
        $this->assertEquals($newLogoUrl, $getResponse->json('data.logo_url'));
    }

    public function test_promotions_forbidden_for_cashier_and_staff(): void
    {
        $cashier = $this->makeEmployee('CASHIER');

        $this->withHeader('Authorization', $this->token($cashier))
            ->postJson('/api/v1/promotions', [
                'title' => 'Unauthorized Promo',
                'discount_type' => 'PERCENTAGE',
                'discount_value' => 10,
                'start_date' => now()->toDateTimeString(),
                'end_date' => now()->addDays(2)->toDateTimeString(),
            ])
            ->assertStatus(403);
    }

    public function test_promotions_full_crud_and_verification(): void
    {
        $manager = $this->makeEmployee('MANAGER');

        $createPayload = [
            'title' => 'Mid Season Sale',
            'promo_code' => 'MID2026',
            'discount_type' => 'PERCENTAGE',
            'discount_value' => 20.0,
            'min_spend' => 50.0,
            'target_department' => 'MEN',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDays(7)->toDateTimeString(),
            'max_usage_count' => 100,
            'is_active' => true,
        ];

        // Manager creates promotion
        $createRes = $this->withHeader('Authorization', $this->token($manager))
            ->postJson('/api/v1/promotions', $createPayload);
        $createRes->assertStatus(201);
        $promoId = $createRes->json('data.promotion_id');

        // Show single promotion
        $showRes = $this->withHeader('Authorization', $this->token($manager))
            ->getJson("/api/v1/promotions/{$promoId}");
        $showRes->assertStatus(200);
        $this->assertEquals('MID2026', $showRes->json('data.promo_code'));

        // Public active promotions list
        $activeRes = $this->getJson('/api/v1/promotions/active');
        $activeRes->assertStatus(200);
        $this->assertNotEmpty($activeRes->json('data'));

        // Public coupon verification
        $verifyRes = $this->postJson('/api/v1/promotions/verify-coupon', [
            'promo_code' => 'MID2026',
            'subtotal' => 100.00,
        ]);
        $verifyRes->assertStatus(200);
        $this->assertEquals(20.00, $verifyRes->json('data.discount_amount'));
        $this->assertEquals(80.00, $verifyRes->json('data.final_total'));

        // Update promotion
        $updateRes = $this->withHeader('Authorization', $this->token($manager))
            ->putJson("/api/v1/promotions/{$promoId}", [
                'title' => 'Extended Season Sale',
                'discount_value' => 25.0,
            ]);
        $updateRes->assertStatus(200);
        $this->assertEquals('Extended Season Sale', $updateRes->json('data.title'));
        $this->assertEquals(25.0, $updateRes->json('data.discount_value'));

        // Delete promotion
        $deleteRes = $this->withHeader('Authorization', $this->token($manager))
            ->deleteJson("/api/v1/promotions/{$promoId}");
        $deleteRes->assertStatus(200);

        $this->assertDatabaseMissing('promotions', ['promotion_id' => $promoId]);
    }

    public function test_product_bundles_forbidden_for_staff(): void
    {
        $staff = $this->makeEmployee('STAFF');
        $variant1 = $this->createVariant();

        $this->withHeader('Authorization', $this->token($staff))
            ->postJson('/api/v1/bundles', [
                'bundle_name' => 'Staff Bundle',
                'sku' => 'BDL-STAFF',
                'bundle_price' => 20.00,
                'items' => [
                    ['variant_id' => $variant1->variant_id, 'quantity' => 1],
                ],
            ])
            ->assertStatus(403);
    }

    public function test_product_bundles_full_crud(): void
    {
        $admin = $this->makeEmployee('ADMIN');

        $variant1 = $this->createVariant();
        $variant2 = $this->createVariant();

        $bundlePayload = [
            'bundle_name' => 'Summer Outfit Duo',
            'sku' => 'BDL-SUMMER-2026',
            'barcode' => '8850001112223',
            'bundle_price' => 39.99,
            'original_total_price' => 50.00,
            'description' => 'Two piece combination pack',
            'image_url' => 'https://res.cloudinary.com/test-cloud/image/upload/bundle-duo.jpg',
            'is_active' => true,
            'items' => [
                ['variant_id' => $variant1->variant_id, 'quantity' => 1],
                ['variant_id' => $variant2->variant_id, 'quantity' => 1],
            ],
        ];

        // Admin creates bundle
        $createRes = $this->withHeader('Authorization', $this->token($admin))
            ->postJson('/api/v1/bundles', $bundlePayload);
        $createRes->assertStatus(201);
        $bundleId = $createRes->json('data.bundle_id');

        // Public show bundle
        $showRes = $this->getJson("/api/v1/bundles/{$bundleId}");
        $showRes->assertStatus(200);
        $this->assertCount(2, $showRes->json('data.items'));

        // Update bundle details and swap items
        $variant3 = $this->createVariant();
        $updateRes = $this->withHeader('Authorization', $this->token($admin))
            ->putJson("/api/v1/bundles/{$bundleId}", [
                'bundle_name' => 'Premium Summer Outfit Trio',
                'bundle_price' => 55.00,
                'items' => [
                    ['variant_id' => $variant1->variant_id, 'quantity' => 1],
                    ['variant_id' => $variant3->variant_id, 'quantity' => 2],
                ],
            ]);
        $updateRes->assertStatus(200);
        $this->assertEquals('Premium Summer Outfit Trio', $updateRes->json('data.bundle_name'));
        $this->assertEquals(55.00, $updateRes->json('data.bundle_price'));
        $this->assertCount(2, $updateRes->json('data.items'));

        // Delete bundle
        $deleteRes = $this->withHeader('Authorization', $this->token($admin))
            ->deleteJson("/api/v1/bundles/{$bundleId}");
        $deleteRes->assertStatus(200);

        $this->assertDatabaseMissing('product_bundles', ['bundle_id' => $bundleId]);
        $this->assertDatabaseMissing('bundle_items', ['bundle_id' => $bundleId]);
    }
}
