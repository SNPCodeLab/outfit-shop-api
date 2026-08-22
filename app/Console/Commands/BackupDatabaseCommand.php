<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database
        {--keep=7 : Number of nightly dumps to retain}
        {--path= : Absolute directory for dumps (default: storage/app/backups)}';

    protected $description = 'Dump the PostgreSQL database to a compressed, timestamped file and prune old backups (nightly RPO strategy)';

    public function handle(): int
    {
        $keep = max(1, (int) $this->option('keep'));
        $dir = (string) ($this->option('path') ?: storage_path('app/backups'));
        $password = (string) config('database.connections.pgsql.password');
        $host = (string) config('database.connections.pgsql.host');
        $port = (string) config('database.connections.pgsql.port');
        $database = (string) config('database.connections.pgsql.database');
        $username = (string) config('database.connections.pgsql.username');

        if (config('database.default') !== 'pgsql' || $database === '') {
            $this->warn('Backup command targets PostgreSQL only; current default connection is "'.config('database.default').'".');

            return self::FAILURE;
        }

        if (! $this->pgDumpAvailable()) {
            $message = 'pg_dump binary not found on this host - install postgresql-client to enable scheduled backups.';
            $this->error($message);
            Log::channel('admin')->error($message);

            return self::FAILURE;
        }

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            $this->error("Cannot create backup directory: {$dir}");

            return self::FAILURE;
        }

        $file = $dir.'/'.now()->format('Ymd_His').'_'.$database.'.sql.gz';
        $cmd = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -Fc %s > %s 2> /dev/null',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($file)
        );

        exec($cmd, $output, $code);

        if ($code !== 0 || ! file_exists($file) || filesize($file) === 0) {
            $message = 'Database backup FAILED (pg_dump exit code '.$code.').';
            $this->error($message);
            Log::channel('admin')->critical($message, ['target' => $file]);

            return self::FAILURE;
        }

        // Prune: keep the newest $keep dumps
        $dumps = glob($dir.'/*_'.$database.'.sql.gz') ?: [];
        sort($dumps);
        foreach (array_slice($dumps, 0, max(0, count($dumps) - $keep)) as $old) {
            @unlink($old);
        }

        $sizeMb = round(filesize($file) / 1048576, 2);
        $this->info("Backup written: {$file} ({$sizeMb} MB), retaining newest {$keep}.");
        Log::channel('admin')->info('Nightly database backup completed', [
            'file' => basename($file),
            'size_mb' => $sizeMb,
            'retained' => $keep,
        ]);

        // Off-site replication hook (S3/R2) can be wired here when credentials
        // are provisioned; local dumps alone do not survive host loss.
        return self::SUCCESS;
    }

    private function pgDumpAvailable(): bool
    {
        exec('command -v pg_dump', $out, $code);

        return $code === 0;
    }
}
