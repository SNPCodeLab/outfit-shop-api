<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use direct PostgreSQL array operations to truncate to 4 words
        // split_part or array_to_string(string_to_array(...)[1:4])
        DB::statement("
            UPDATE products
            SET product_name = array_to_string((string_to_array(product_name, ' '))[1:4], ' ')
            WHERE array_length(string_to_array(product_name, ' '), 1) > 4
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible
    }
};
