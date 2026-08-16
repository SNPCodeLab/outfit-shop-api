<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleDisplaySeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────────────
        // 1. MASTER CATEGORIES (15 Core Omnichannel Departments)
        // ─────────────────────────────────────────────────────────────────────
        $allCategories = [
            ['name' => 'Dresses & Evening Gowns',      'slug' => 'dresses-gowns',          'type' => 'STANDARD_PHYSICAL', 'desc' => 'High-end silk evening gowns and crinoline dresses.'],
            ['name' => 'Tops & Silk Blouses',          'slug' => 'tops-silk-blouses',      'type' => 'STANDARD_PHYSICAL', 'desc' => 'Tailored embroidered shirts, poplin blouses, and silk tops.'],
            ['name' => 'Bottoms, Shorts & Culottes',   'slug' => 'bottoms-culottes',       'type' => 'STANDARD_PHYSICAL', 'desc' => 'Monogram silk culottes, tailored shorts, and twill trousers.'],
            ['name' => 'Skirts & Flare Skirts',        'slug' => 'skirts-flares',          'type' => 'STANDARD_PHYSICAL', 'desc' => 'Crinoline flare skirts and fluted knit midi skirts.'],
            ['name' => 'Outerwear & Tailored Jackets', 'slug' => 'outerwear-jackets',      'type' => 'STANDARD_PHYSICAL', 'desc' => 'Collared tailored jackets and luxury outerwear.'],
            ['name' => 'Knitwear & Sweaters',          'slug' => 'knitwear-sweaters',      'type' => 'STANDARD_PHYSICAL', 'desc' => 'Fine-gauge knit sweaters and woven wool tops.'],
            ['name' => 'Luxury Leather Bags',          'slug' => 'luxury-bags',            'type' => 'STANDARD_PHYSICAL', 'desc' => 'Mini T-Lock crossbody bags, leather totes, and clutches.'],
            ['name' => 'Footwear & Leather Shoes',     'slug' => 'footwear-shoes',         'type' => 'STANDARD_PHYSICAL', 'desc' => 'T-Strap nappa leather sandals and buttercup ballerinas.'],
            ['name' => 'Monogram Silk Accessories',    'slug' => 'monogram-accessories',   'type' => 'STANDARD_PHYSICAL', 'desc' => 'Embroidered monogram silk scarves and luxury accessories.'],
            ['name' => "Men's Designer Collection",    'slug' => 'mens-designer',          'type' => 'STANDARD_PHYSICAL', 'desc' => "Designer classic embroidered polo shirts and activewear."],
            ['name' => 'Craft Beers & Cold Beverages', 'slug' => 'beverages-drinks',       'type' => 'FMCG_EXPIRABLE',    'desc' => 'Premium Cambodian craft beers, sodas, and mineral water.'],
            ['name' => 'Packaged Foods & Snacks',      'slug' => 'packaged-foods',         'type' => 'FMCG_EXPIRABLE',    'desc' => 'Consumer packaged snacks and gourmet pantry foods.'],
            ['name' => 'Digital eBooks & Publications','slug' => 'digital-ebooks',         'type' => 'DIGITAL_DOWNLOAD',  'desc' => 'Downloadable technical books, design guides, and eBooks.'],
            ['name' => 'Skincare & Cosmetics',         'slug' => 'skincare-cosmetics',     'type' => 'FMCG_EXPIRABLE',    'desc' => 'Luxury skincare, face serums, and beauty cosmetics.'],
            ['name' => 'Pharmacy & Wellness',          'slug' => 'pharmacy-wellness',      'type' => 'FMCG_EXPIRABLE',    'desc' => 'Health vitamins, supplements, and daily wellness items.'],
        ];

        $categories = [];
        foreach ($allCategories as $cat) {
            $categories[$cat['slug']] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'category_name' => $cat['name'],
                    'description'   => $cat['desc'],
                    'is_active'     => true,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. MASTER SIZES & MEASUREMENTS
        // ─────────────────────────────────────────────────────────────────────
        $sizes = [
            'S'     => ClothingSize::firstOrCreate(['size_code' => 'S'],     ['size_name' => 'Small', 'sort_order' => 1]),
            'M'     => ClothingSize::firstOrCreate(['size_code' => 'M'],     ['size_name' => 'Medium', 'sort_order' => 2]),
            'L'     => ClothingSize::firstOrCreate(['size_code' => 'L'],     ['size_name' => 'Large', 'sort_order' => 3]),
            'XL'    => ClothingSize::firstOrCreate(['size_code' => 'XL'],    ['size_name' => 'Extra Large', 'sort_order' => 4]),
            'EU37'  => ClothingSize::firstOrCreate(['size_code' => 'EU37'],  ['size_name' => 'EU 37', 'sort_order' => 5]),
            'EU38'  => ClothingSize::firstOrCreate(['size_code' => 'EU38'],  ['size_name' => 'EU 38', 'sort_order' => 6]),
            'EU39'  => ClothingSize::firstOrCreate(['size_code' => 'EU39'],  ['size_name' => 'EU 39', 'sort_order' => 7]),
            'STD'   => ClothingSize::firstOrCreate(['size_code' => 'STD'],   ['size_name' => 'Standard / Free Size', 'sort_order' => 8]),
            'PDF'   => ClothingSize::firstOrCreate(['size_code' => 'PDF'],   ['size_name' => 'Digital PDF Edition', 'sort_order' => 9]),
            'CAN'   => ClothingSize::firstOrCreate(['size_code' => '330ML'], ['size_name' => '330ml Can', 'sort_order' => 10]),
            'BTL'   => ClothingSize::firstOrCreate(['size_code' => '500ML'], ['size_name' => '500ml Bottle', 'sort_order' => 11]),
            '30ML'  => ClothingSize::firstOrCreate(['size_code' => '30ML'],  ['size_name' => '30ml Bottle', 'sort_order' => 12]),
            '60CAP' => ClothingSize::firstOrCreate(['size_code' => '60CAP'], ['size_name' => '60 Capsules', 'sort_order' => 13]),
        ];

        // ─────────────────────────────────────────────────────────────────────
        // 3. MASTER LUXURY COLORS
        // ─────────────────────────────────────────────────────────────────────
        $colors = [
            'BLK' => Color::firstOrCreate(['color_code' => 'BLK'], ['color_name' => 'Noir Black',        'hex_code' => '#000000']),
            'IVR' => Color::firstOrCreate(['color_code' => 'IVR'], ['color_name' => 'Ivory White',       'hex_code' => '#FFFFF0']),
            'TER' => Color::firstOrCreate(['color_code' => 'TER'], ['color_name' => 'Terracotta Clay',   'hex_code' => '#E2725B']),
            'NVY' => Color::firstOrCreate(['color_code' => 'NVY'], ['color_name' => 'Midnight Navy',     'hex_code' => '#000080']),
            'SGE' => Color::firstOrCreate(['color_code' => 'SGE'], ['color_name' => 'Sage Green',        'hex_code' => '#87A96B']),
            'BGE' => Color::firstOrCreate(['color_code' => 'BGE'], ['color_name' => 'Cream Beige',       'hex_code' => '#F5F5DC']),
            'YEL' => Color::firstOrCreate(['color_code' => 'YEL'], ['color_name' => 'Buttercup Yellow',  'hex_code' => '#FEE227']),
            'BRN' => Color::firstOrCreate(['color_code' => 'BRN'], ['color_name' => 'Dark Brown',        'hex_code' => '#4A2E18']),
            'TAU' => Color::firstOrCreate(['color_code' => 'TAU'], ['color_name' => 'Warm Taupe',        'hex_code' => '#B38B6D']),
            'GLD' => Color::firstOrCreate(['color_code' => 'GLD'], ['color_name' => 'Canary Gold',       'hex_code' => '#FFD700']),
            'UNI' => Color::firstOrCreate(['color_code' => 'UNI'], ['color_name' => 'Standard Universal','hex_code' => '#FFFFFF']),
        ];

        // ─────────────────────────────────────────────────────────────────────
        // 4. MASTER BRANDS
        // ─────────────────────────────────────────────────────────────────────
        $brandKhmeriel = Brand::firstOrCreate(
            ['slug' => 'khmeriel'],
            ['brand_name' => 'KhmeRiel Signature', 'country_of_origin' => 'Cambodia', 'is_active' => true]
        );
        $brandRL = Brand::firstOrCreate(
            ['slug' => 'ralph-lauren-rlx'],
            ['brand_name' => 'Ralph Lauren RLX', 'country_of_origin' => 'USA', 'is_active' => true]
        );
        $brandVattanac = Brand::firstOrCreate(
            ['slug' => 'vattanac'],
            ['brand_name' => 'Vattanac Brewery', 'country_of_origin' => 'Cambodia', 'is_active' => true]
        );
        $brandKwd = Brand::firstOrCreate(
            ['slug' => 'kwd-publishing'],
            ['brand_name' => 'KWD Publishing House', 'country_of_origin' => 'Cambodia', 'is_active' => true]
        );
        $brandKesararam = Brand::firstOrCreate(
            ['slug' => 'kesararam-wellness'],
            ['brand_name' => 'Kesararam Health & Beauty', 'country_of_origin' => 'Cambodia', 'is_active' => true]
        );

        // ─────────────────────────────────────────────────────────────────────
        // 5. 18 OMNICHANNEL SHOWCASE PRODUCTS (3 VARIANTS EACH = 54 SKUs)
        // ─────────────────────────────────────────────────────────────────────
        $displayProducts = [
            // 1. Column Gown
            [
                'cat_slug'     => 'dresses-gowns',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Silk Evening Column Gown',
                'desc_en'      => 'Floor-length pure silk evening column gown tailored for formal galas.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905171/KHMERIEL_DRESSES_CRINOLINE_COLUMN_GOWN_TERRACOTTA_LOOK_cloth_096.png',
                'public_id'    => 'KHMERIEL_DRESSES_CRINOLINE_COLUMN_GOWN_TERRACOTTA_LOOK_cloth_096',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'TER', 'sku' => 'DRS-096-TER-S', 'cost' => 45.00, 'price' => 125.00, 'qty' => 18],
                    ['size' => 'M', 'color' => 'TER', 'sku' => 'DRS-096-TER-M', 'cost' => 45.00, 'price' => 125.00, 'qty' => 24],
                    ['size' => 'L', 'color' => 'BLK', 'sku' => 'DRS-096-BLK-L', 'cost' => 48.00, 'price' => 135.00, 'qty' => 12],
                ]
            ],
            // 2. Backless Evening Dress
            [
                'cat_slug'     => 'dresses-gowns',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Backless Silk Halter Evening Gown',
                'desc_en'      => 'Dramatic backless evening gown cut on the bias from high-sheen silk charmeuse.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905086/KHMERIEL_DRESSES_BACKLESS_SILK_EVENING_DRESS_BLACK_LOOK_cloth_059.png',
                'public_id'    => 'KHMERIEL_DRESSES_BACKLESS_SILK_EVENING_DRESS_BLACK_LOOK_cloth_059',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'BLK', 'sku' => 'DRS-059-BLK-S', 'cost' => 50.00, 'price' => 140.00, 'qty' => 15],
                    ['size' => 'M', 'color' => 'BLK', 'sku' => 'DRS-059-BLK-M', 'cost' => 50.00, 'price' => 140.00, 'qty' => 20],
                    ['size' => 'L', 'color' => 'IVR', 'sku' => 'DRS-059-IVR-L', 'cost' => 50.00, 'price' => 140.00, 'qty' => 16],
                ]
            ],
            // 3. Fluid Draped Blouse
            [
                'cat_slug'     => 'tops-silk-blouses',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Fluid Draped Silk Blouse',
                'desc_en'      => 'Relaxed silhouette fluid draped luxury blouse with flowing shoulder lines.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905145/KHMERIEL_TOPS_FLUID_DRAPED_SILK_BLOUSE_CREAM_BEIGE_LOOK_cloth_012.png',
                'public_id'    => 'KHMERIEL_TOPS_FLUID_DRAPED_SILK_BLOUSE_CREAM_BEIGE_LOOK_cloth_012',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'BGE', 'sku' => 'TOP-012-BGE-S', 'cost' => 22.00, 'price' => 58.00, 'qty' => 35],
                    ['size' => 'M', 'color' => 'BGE', 'sku' => 'TOP-012-BGE-M', 'cost' => 22.00, 'price' => 58.00, 'qty' => 40],
                    ['size' => 'L', 'color' => 'IVR', 'sku' => 'TOP-012-IVR-L', 'cost' => 22.00, 'price' => 58.00, 'qty' => 28],
                ]
            ],
            // 4. Classic Embroidered Silk Shirt
            [
                'cat_slug'     => 'tops-silk-blouses',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Classic Embroidered Silk Shirt',
                'desc_en'      => 'Signature tailored silk-cotton button-up shirt with tonal monogram embroidery.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905084/KHMERIEL_TOPS_CLASSIC_EMBROIDERED_SILK_SHIRT_BLACK_LOOK_cloth_032.png',
                'public_id'    => 'KHMERIEL_TOPS_CLASSIC_EMBROIDERED_SILK_SHIRT_BLACK_LOOK_cloth_032',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'BLK', 'sku' => 'TOP-032-BLK-S', 'cost' => 25.00, 'price' => 68.00, 'qty' => 30],
                    ['size' => 'M', 'color' => 'BLK', 'sku' => 'TOP-032-BLK-M', 'cost' => 25.00, 'price' => 68.00, 'qty' => 45],
                    ['size' => 'L', 'color' => 'IVR', 'sku' => 'TOP-032-IVR-L', 'cost' => 25.00, 'price' => 68.00, 'qty' => 32],
                ]
            ],
            // 5. Monogram Silk Culottes
            [
                'cat_slug'     => 'bottoms-culottes',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Monogram Silk PJ Culottes',
                'desc_en'      => 'Wide-leg silk lounge culottes featuring all-over monogram jacquard.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905186/KHMERIEL_BOTTOMS_MONOGRAM_SILK_CULOTTES_IVORY_LOOK_cloth_277.png',
                'public_id'    => 'KHMERIEL_BOTTOMS_MONOGRAM_SILK_CULOTTES_IVORY_LOOK_cloth_277',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'IVR', 'sku' => 'BOT-277-IVR-S', 'cost' => 28.00, 'price' => 75.00, 'qty' => 20],
                    ['size' => 'M', 'color' => 'IVR', 'sku' => 'BOT-277-IVR-M', 'cost' => 28.00, 'price' => 75.00, 'qty' => 30],
                    ['size' => 'L', 'color' => 'BLK', 'sku' => 'BOT-277-BLK-L', 'cost' => 28.00, 'price' => 75.00, 'qty' => 22],
                ]
            ],
            // 6. Crinoline Flare Skirt
            [
                'cat_slug'     => 'skirts-flares',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Crinoline Flare Midi Skirt',
                'desc_en'      => 'Architectural flare skirt crafted with structured crinoline weave.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905197/KHMERIEL_SKIRTS_CRINOLINE_FLARE_SKIRT_TERRACOTTA_LOOK_cloth_298.png',
                'public_id'    => 'KHMERIEL_SKIRTS_CRINOLINE_FLARE_SKIRT_TERRACOTTA_LOOK_cloth_298',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'TER', 'sku' => 'SKT-298-TER-S', 'cost' => 30.00, 'price' => 88.00, 'qty' => 15],
                    ['size' => 'M', 'color' => 'TER', 'sku' => 'SKT-298-TER-M', 'cost' => 30.00, 'price' => 88.00, 'qty' => 25],
                    ['size' => 'L', 'color' => 'BLK', 'sku' => 'SKT-298-BLK-L', 'cost' => 30.00, 'price' => 88.00, 'qty' => 14],
                ]
            ],
            // 7. Fluted Knit Midi Skirt
            [
                'cat_slug'     => 'skirts-flares',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Fluted Knit Midi Skirt',
                'desc_en'      => 'Heavyweight ribbed knit midi skirt with an elegant fluted silhouette hem.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905171/KHMERIEL_SKIRTS_FLUTED_KNIT_MIDI_SKIRT_BLACK_LOOK_cloth_313.png',
                'public_id'    => 'KHMERIEL_SKIRTS_FLUTED_KNIT_MIDI_SKIRT_BLACK_LOOK_cloth_313',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'BLK', 'sku' => 'SKT-313-BLK-S', 'cost' => 28.00, 'price' => 82.00, 'qty' => 20],
                    ['size' => 'M', 'color' => 'BLK', 'sku' => 'SKT-313-BLK-M', 'cost' => 28.00, 'price' => 82.00, 'qty' => 30],
                    ['size' => 'L', 'color' => 'BLK', 'sku' => 'SKT-313-BLK-L', 'cost' => 28.00, 'price' => 82.00, 'qty' => 18],
                ]
            ],
            // 8. Tailored Jacket
            [
                'cat_slug'     => 'outerwear-jackets',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Collared Tailored Jacket',
                'desc_en'      => 'Single-breasted tailored outerwear jacket with structured shoulder pads.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905170/KHMERIEL_OUTERWEAR_COLLARED_TAILORED_JACKET_SAGE_GREEN_LOOK_cloth_047.png',
                'public_id'    => 'KHMERIEL_OUTERWEAR_COLLARED_TAILORED_JACKET_SAGE_GREEN_LOOK_cloth_047',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'M',  'color' => 'SGE', 'sku' => 'OUT-047-SGE-M', 'cost' => 60.00, 'price' => 165.00, 'qty' => 12],
                    ['size' => 'L',  'color' => 'SGE', 'sku' => 'OUT-047-SGE-L', 'cost' => 60.00, 'price' => 165.00, 'qty' => 16],
                    ['size' => 'XL', 'color' => 'BLK', 'sku' => 'OUT-047-BLK-XL','cost' => 62.00, 'price' => 175.00, 'qty' => 10],
                ]
            ],
            // 9. Fine Gauge Knit Sweater
            [
                'cat_slug'     => 'knitwear-sweaters',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Fine Gauge Knit Sweater',
                'desc_en'      => 'Ultra-soft fine gauge knit crewneck sweater for trans-seasonal layering.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905149/KHMERIEL_KNITWEAR_FINE_GAUGE_KNIT_SWEATER_TERRACOTTA_LOOK_cloth_349.png',
                'public_id'    => 'KHMERIEL_KNITWEAR_FINE_GAUGE_KNIT_SWEATER_TERRACOTTA_LOOK_cloth_349',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'TER', 'sku' => 'KNT-349-TER-S', 'cost' => 26.00, 'price' => 68.00, 'qty' => 25],
                    ['size' => 'M', 'color' => 'TER', 'sku' => 'KNT-349-TER-M', 'cost' => 26.00, 'price' => 68.00, 'qty' => 35],
                    ['size' => 'L', 'color' => 'IVR', 'sku' => 'KNT-349-IVR-L', 'cost' => 26.00, 'price' => 68.00, 'qty' => 20],
                ]
            ],
            // 10. Mini T-Lock Leather Crossbody Bag
            [
                'cat_slug'     => 'luxury-bags',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Mini T-Lock Leather Crossbody Bag',
                'desc_en'      => 'Italian-tanned smooth calfskin crossbody bag with polished metal T-lock closure.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905158/KHMERIEL_BAGS_MINI_T_LOCK_CROSSBODY_BAG_BLACK_LOOK_cloth_128.png',
                'public_id'    => 'KHMERIEL_BAGS_MINI_T_LOCK_CROSSBODY_BAG_BLACK_LOOK_cloth_128',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'STD', 'color' => 'BLK', 'sku' => 'BAG-128-BLK-STD', 'cost' => 55.00, 'price' => 145.00, 'qty' => 14],
                    ['size' => 'STD', 'color' => 'IVR', 'sku' => 'BAG-128-IVR-STD', 'cost' => 55.00, 'price' => 145.00, 'qty' => 16],
                    ['size' => 'STD', 'color' => 'TER', 'sku' => 'BAG-128-TER-STD', 'cost' => 55.00, 'price' => 145.00, 'qty' => 10],
                ]
            ],
            // 11. Three-Compartment Leather Tote Bag
            [
                'cat_slug'     => 'luxury-bags',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Three-Compartment Business Leather Tote',
                'desc_en'      => 'Spacious structured leather tote bag featuring triple internal divider compartments.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905156/KHMERIEL_BAGS_THREE_COMPARTMENT_LEATHER_TOTE_BLACK_LOOK_cloth_143.png',
                'public_id'    => 'KHMERIEL_BAGS_THREE_COMPARTMENT_LEATHER_TOTE_BLACK_LOOK_cloth_143',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'STD', 'color' => 'BLK', 'sku' => 'BAG-143-BLK-STD', 'cost' => 75.00, 'price' => 195.00, 'qty' => 12],
                    ['size' => 'STD', 'color' => 'TAU', 'sku' => 'BAG-143-TAU-STD', 'cost' => 75.00, 'price' => 195.00, 'qty' => 15],
                    ['size' => 'STD', 'color' => 'BRN', 'sku' => 'BAG-143-BRN-STD', 'cost' => 75.00, 'price' => 195.00, 'qty' => 8],
                ]
            ],
            // 12. T-Strap Nappa Leather Sandals
            [
                'cat_slug'     => 'footwear-shoes',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel T-Strap Nappa Leather Sandals',
                'desc_en'      => 'Handcrafted buttery nappa leather summer sandals with minimal T-strap buckle.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905154/KHMERIEL_SHOES_T_STRAP_NAPPA_LEATHER_SANDALS_DARK_BROWN_LOOK_cloth_180.png',
                'public_id'    => 'KHMERIEL_SHOES_T_STRAP_NAPPA_LEATHER_SANDALS_DARK_BROWN_LOOK_cloth_180',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'EU37', 'color' => 'BRN', 'sku' => 'SHO-180-BRN-37', 'cost' => 38.00, 'price' => 98.00, 'qty' => 12],
                    ['size' => 'EU38', 'color' => 'BRN', 'sku' => 'SHO-180-BRN-38', 'cost' => 38.00, 'price' => 98.00, 'qty' => 18],
                    ['size' => 'EU39', 'color' => 'BLK', 'sku' => 'SHO-180-BLK-39', 'cost' => 38.00, 'price' => 98.00, 'qty' => 14],
                ]
            ],
            // 13. Naplack Leather Ballerinas
            [
                'cat_slug'     => 'footwear-shoes',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Naplack Leather Ballet Flats',
                'desc_en'      => 'Cushioned patent naplack leather ballerinas with flexible rubber outsoles.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905080/KHMERIEL_SHOES_NAPLACK_LEATHER_BALLERINAS_BUTTERCUP_YELLOW_LOOK_cloth_162.png',
                'public_id'    => 'KHMERIEL_SHOES_NAPLACK_LEATHER_BALLERINAS_BUTTERCUP_YELLOW_LOOK_cloth_162',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'EU37', 'color' => 'YEL', 'sku' => 'SHO-162-YEL-37', 'cost' => 35.00, 'price' => 92.00, 'qty' => 15],
                    ['size' => 'EU38', 'color' => 'YEL', 'sku' => 'SHO-162-YEL-38', 'cost' => 35.00, 'price' => 92.00, 'qty' => 20],
                    ['size' => 'EU39', 'color' => 'BLK', 'sku' => 'SHO-162-BLK-39', 'cost' => 35.00, 'price' => 92.00, 'qty' => 16],
                ]
            ],
            // 14. Embroidered Monogram Silk Scarf
            [
                'cat_slug'     => 'monogram-accessories',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Embroidered Monogram Silk Scarf',
                'desc_en'      => 'Square 90x90cm pure mulberry silk twill scarf with hand-rolled edges.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905148/KHMERIEL_ACCESSORIES_EMBROIDERED_MONOGRAM_SILK_SCARF_BLACK_LOOK_cloth_377.png',
                'public_id'    => 'KHMERIEL_ACCESSORIES_EMBROIDERED_MONOGRAM_SILK_SCARF_BLACK_LOOK_cloth_377',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'STD', 'color' => 'BLK', 'sku' => 'ACC-377-BLK-STD', 'cost' => 18.00, 'price' => 45.00, 'qty' => 30],
                    ['size' => 'STD', 'color' => 'IVR', 'sku' => 'ACC-377-IVR-STD', 'cost' => 18.00, 'price' => 45.00, 'qty' => 25],
                    ['size' => 'STD', 'color' => 'TER', 'sku' => 'ACC-377-TER-STD', 'cost' => 18.00, 'price' => 45.00, 'qty' => 20],
                ]
            ],
            // 15. Men's Classic Polo
            [
                'cat_slug'     => 'mens-designer',
                'brand_id'     => $brandRL->brand_id,
                'name_en'      => 'KhmeRiel Classic Embroidered Cotton Polo',
                'desc_en'      => '100% long-staple pima cotton breathable pique polo with mother-of-pearl buttons.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786904788/KHMERIEL_MENS_CLASSIC_POLO_M_POLO_HUB_C06G_IMG_cloth_053.png',
                'public_id'    => 'KHMERIEL_MENS_CLASSIC_POLO_M_POLO_HUB_C06G_IMG_cloth_053',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'M',  'color' => 'NVY', 'sku' => 'POLO-053-NVY-M', 'cost' => 24.00, 'price' => 65.00, 'qty' => 40],
                    ['size' => 'L',  'color' => 'NVY', 'sku' => 'POLO-053-NVY-L', 'cost' => 24.00, 'price' => 65.00, 'qty' => 50],
                    ['size' => 'XL', 'color' => 'BLK', 'sku' => 'POLO-053-BLK-XL','cost' => 24.00, 'price' => 65.00, 'qty' => 30],
                ]
            ],
            // 16. Craft Beer (FMCG Expirable)
            [
                'cat_slug'     => 'beverages-drinks',
                'brand_id'     => $brandVattanac->brand_id,
                'name_en'      => 'Vattanac Premium Craft Beer 330ml',
                'desc_en'      => 'Crisp Cambodian luxury lager brewed from 100% pure malt and noble European hops.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png',
                'public_id'    => 'KhmerRiel_Beer_Vattanac_330ml',
                'product_type' => 'FMCG_EXPIRABLE',
                'variants'     => [
                    ['size' => 'CAN', 'color' => 'GLD', 'sku' => 'BEER-VAT-330-CAN',  'cost' => 0.85, 'price' => 1.75,  'qty' => 120],
                    ['size' => 'STD', 'color' => 'GLD', 'sku' => 'BEER-VAT-6PACK',    'cost' => 4.80, 'price' => 9.50,  'qty' => 40],
                    ['size' => 'STD', 'color' => 'GLD', 'sku' => 'BEER-VAT-CARTON24', 'cost' => 18.50,'price' => 32.00, 'qty' => 25],
                ]
            ],
            // 17. Digital Publication eBook
            [
                'cat_slug'     => 'digital-ebooks',
                'brand_id'     => $brandKwd->brand_id,
                'name_en'      => 'Cloud Networking Concepts Master Handbook',
                'desc_en'      => 'Complete digital handbook covering full-stack cloud infrastructure and networking protocols.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905169/KHMERIEL_DIGITAL_BOOK_PUBLICATION_EN_A_TYPOLOGY_OF_VERBAL_BORROWINGS_JAN_WOHLGEMUTH_cloth_389.pdf',
                'public_id'    => 'KHMERIEL_DIGITAL_BOOK_PUBLICATION_EN_A_TYPOLOGY_OF_VERBAL_BORROWINGS_JAN_WOHLGEMUTH_cloth_389',
                'product_type' => 'DIGITAL_DOWNLOAD',
                'variants'     => [
                    ['size' => 'PDF', 'color' => 'UNI', 'sku' => 'EBOOK-NET-DIGITAL', 'cost' => 0.00, 'price' => 15.00, 'qty' => 999],
                    ['size' => 'STD', 'color' => 'UNI', 'sku' => 'EBOOK-NET-STUDENT', 'cost' => 0.00, 'price' => 9.99,  'qty' => 999],
                    ['size' => 'STD', 'color' => 'UNI', 'sku' => 'EBOOK-NET-ENTERPRISE','cost' => 0.00, 'price' => 49.00, 'qty' => 999],
                ]
            ],
            // 18. Organic Silk Facial Serum (Cosmetics)
            [
                'cat_slug'     => 'skincare-cosmetics',
                'brand_id'     => $brandKesararam->brand_id,
                'name_en'      => 'Kesararam Organic Silk Glow Facial Serum 30ml',
                'desc_en'      => 'Intensive hydrating serum infused with raw Cambodian golden silk sericin and botanical antioxidants.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png',
                'public_id'    => 'Kesararam_Silk_Serum_30ml',
                'product_type' => 'FMCG_EXPIRABLE',
                'variants'     => [
                    ['size' => '30ML', 'color' => 'GLD', 'sku' => 'SERUM-SILK-30ML',  'cost' => 9.50,  'price' => 24.00, 'qty' => 60],
                    ['size' => 'STD',  'color' => 'GLD', 'sku' => 'SERUM-SILK-DUO',   'cost' => 17.00, 'price' => 42.00, 'qty' => 30],
                    ['size' => 'STD',  'color' => 'GLD', 'sku' => 'SERUM-SILK-VIP-SET','cost' => 28.00, 'price' => 68.00, 'qty' => 20],
                ]
            ],
        ];

        foreach ($displayProducts as $index => $pData) {
            $cat = $categories[$pData['cat_slug']];

            $product = Product::create([
                'product_name'     => $pData['name_en'],
                'slug'             => Str::slug($pData['name_en']) . '-' . ($index + 1),
                'category_id'      => $cat->category_id,
                'brand_id'         => $pData['brand_id'],
                'product_type'     => $pData['product_type'],
                'description'      => $pData['desc_en'],
                'image_url'        => $pData['image_url'],
                'image_public_id'  => $pData['public_id'],
                'is_featured'      => $index < 10,
                'status'           => 'ACTIVE',
            ]);

            // Add Product Image entry
            ProductImage::create([
                'product_id'       => $product->product_id,
                'image_url'        => $pData['image_url'],
                'image_public_id'  => $pData['public_id'],
                'is_primary'       => true,
                'sort_order'       => 0,
            ]);

            // Add 3 Variants for this Product
            foreach ($pData['variants'] as $vIndex => $vData) {
                $size = $sizes[$vData['size']];
                $color = $colors[$vData['color']];

                $barcode = '885' . str_pad((string)($product->product_id * 100 + $vIndex + 1), 9, '0', STR_PAD_LEFT);

                ProductVariant::create([
                    'product_id'       => $product->product_id,
                    'sku'              => $vData['sku'],
                    'barcode'          => $barcode,
                    'size_id'          => $size->size_id,
                    'color_id'         => $color->color_id,
                    'cost_price'       => $vData['cost'],
                    'sale_price'       => $vData['price'],
                    'quantity'         => $vData['qty'],
                    'reorder_level'    => 10,
                    'image_url'        => $pData['image_url'],
                    'image_public_id'  => $pData['public_id'],
                    'status'           => 'ACTIVE',
                ]);
            }
        }
    }
}
