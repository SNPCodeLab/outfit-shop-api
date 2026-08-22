<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Runs the luxury-brand catalog SQL scripts (Ralph Lauren, Gucci, LV ...)
 * through the standard Laravel seeding lifecycle instead of manual pasting
 * into the Neon query editor. Idempotent-ish: each file only executes when
 * its brand is missing from the categories table, so re-seeding is safe.
 *
 * Usage: php artisan db:seed --class=BrandCatalogSeeder
 */
class BrandCatalogSeeder extends Seeder
{
    /**
     * Brand marker per SQL file, used as the already-seeded guard.
     */
    private const FILE_BRAND_MARKERS = [
        'part1_ralph_lauren_rlx.sql' => 'RALPH',
        'part2_gucci.sql' => 'GUCCI',
        'part3_chanel.sql' => 'CHANEL',
        'part4_prada.sql' => 'PRADA',
        'part5_burberry.sql' => 'BURBERRY',
        'part6_louis_vuitton.sql' => 'LOUIS',
    ];

    public function run(): void
    {
        $sqlDir = database_path('sql');

        if (! is_dir($sqlDir)) {
            $this->command?->warn('database/sql directory not found - nothing to seed.');

            return;
        }

        foreach (glob($sqlDir.'/*.sql') ?: [] as $file) {
            $basename = basename($file);
            $marker = self::FILE_BRAND_MARKERS[$basename]
                ?? strtoupper(str_replace(['part', '.sql', '_'], ['', '', ' '], $basename));

            if ($this->alreadySeeded($marker)) {
                $this->command?->line("Skipping {$basename} (brand '{$marker}' already present).");

                continue;
            }

            $this->command?->line("Seeding {$basename} ...");
            DB::unprepared(file_get_contents($file));
            $this->command?->info("Seeded {$basename}.");
        }
    }

    private function alreadySeeded(string $marker): bool
    {
        try {
            return Schema::hasTable('categories')
                && DB::table('categories')
                    ->whereRaw('LOWER(category_name) LIKE ?', ['%'.strtolower($marker).'%'])
                    ->exists();
        } catch (\Throwable) {
            return false;
        }
    }
}
