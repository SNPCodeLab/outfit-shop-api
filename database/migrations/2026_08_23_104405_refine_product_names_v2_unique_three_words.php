<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to clean product names (3 words, no codes, unique).
     */
    public function up(): void
    {
        // 1. Raw SQL Clean (Remove words with digits and truncate to 3 words)
        DB::statement("UPDATE products SET product_name = trim(regexp_replace(product_name, '\S*\d\S*', '', 'g'))");
        DB::statement("UPDATE products SET product_name = array_to_string((string_to_array(trim(regexp_replace(product_name, '\s+', ' ', 'g')), ' '))[1:3], ' ')");
        DB::statement("UPDATE products SET product_name = 'Premium Product Item' WHERE product_name IS NULL OR trim(product_name) = ''");

        // 2. Uniqueness Pass (Strictly 3 words)
        $seen = [];
        Product::orderBy('product_id')->each(function ($product) use (&$seen) {
            $name = $product->product_name;

            if (isset($seen[$name])) {
                // To keep 3 words, truncate base to 2 and add ID as 3rd
                $words = explode(' ', $name);
                $uniqueName = trim(implode(' ', array_slice($words, 0, 2)).' '.$product->product_id);
                DB::table('products')->where('product_id', $product->product_id)->update(['product_name' => $uniqueName]);
                $seen[$uniqueName] = true;
            } else {
                $seen[$name] = true;
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible
    }
};
