<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CloudinarySyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncCloudinaryImages extends Command
{
    protected $signature = 'cloudinary:sync {--report-only : Only generate the missing images report}';
    protected $description = 'Synchronize brand images from Cloudinary to the local database with strict deduplication.';

    public function handle(CloudinarySyncService $syncService): int
    {
        if ($this->option('report-only')) {
            $this->generateReport($syncService);
            return 0;
        }

        $this->info('Starting Cloudinary brand image synchronization...');

        $stats = $syncService->syncAllBrands();

        $this->table(['Inserted', 'Skipped', 'Updated', 'Errors'], [[
            $stats['inserted'],
            $stats['skipped'],
            $stats['updated'],
            $stats['errors']
        ]]);

        $this->generateSyncLog($stats);
        $this->info('Synchronization complete. Log saved to sync_log.md.');

        return 0;
    }

    protected function generateReport(CloudinarySyncService $syncService): void
    {
        $report = $syncService->getMissingImagesReport();

        if (empty($report)) {
            $this->info('All products for all brands have associated images.');
            return;
        }

        $this->warn('Missing Images Report:');
        $this->table(['Brand ID', 'Brand Name', 'Products', 'With Images', 'Missing'], array_map(fn($r) => (array)$r, $report));
    }

    protected function generateSyncLog(array $stats): void
    {
        $logPath = base_path('sync_log.md');
        $content = "# Cloudinary Sync Log - " . now()->toDateTimeString() . "\n\n";
        $content .= "## Summary\n";
        $content .= "- **Inserted:** {$stats['inserted']}\n";
        $content .= "- **Skipped:** {$stats['skipped']}\n";
        $content .= "- **Updated:** {$stats['updated']}\n";
        $content .= "- **Errors:** {$stats['errors']}\n\n";

        $content .= "## Details per Brand\n";
        foreach ($stats['details'] as $detail) {
            $content .= "### {$detail['brand']}\n";
            $content .= "- Inserted: {$detail['result']['inserted']}\n";
            $content .= "- Skipped: {$detail['result']['skipped']}\n";
            $content .= "- Updated: {$detail['result']['updated']}\n\n";
        }

        File::put($logPath, $content);
    }
}
