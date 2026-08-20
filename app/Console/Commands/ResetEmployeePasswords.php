<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * ResetEmployeePasswords
 *
 * Resets the password_hash for all 4 standard RBAC employee accounts
 * to their canonical production credentials.
 *
 * Usage:
 *   php artisan employees:reset-passwords
 *   php artisan employees:reset-passwords --dry-run
 */
class ResetEmployeePasswords extends Command
{
    protected $signature = 'employees:reset-passwords {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Reset all standard employee account passwords to their production credentials';

    /**
     * Canonical production credentials.
     * Keep in sync with RolesAndPermissionsSeeder and LOCAL_CREDENTIALS.md.
     */
    private const CREDENTIALS = [
        'admin' => 'Admin#Secure#2026',
        'manager' => 'Manager@Ops!2026',
        'cashier' => 'Cashier$Point$2026',
        'staff' => 'Staff%Store%2026',
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('[DRY RUN] No changes will be written to the database.');
        }

        $this->info('Resetting employee passwords...');
        $this->newLine();

        $rows = [];
        $successCount = 0;
        $failCount = 0;

        foreach (self::CREDENTIALS as $username => $password) {
            $employee = Employee::where('username', $username)->first();

            if (! $employee) {
                $rows[] = [$username, 'NOT FOUND', 'SKIPPED'];
                $failCount++;

                continue;
            }

            if (! $isDryRun) {
                try {
                    $employee->update([
                        'password_hash' => Hash::make($password),
                    ]);
                    $rows[] = [$username, $employee->email, 'RESET OK'];
                    $successCount++;
                } catch (\Throwable $e) {
                    $rows[] = [$username, $employee->email, 'FAILED: '.$e->getMessage()];
                    $failCount++;
                }
            } else {
                $rows[] = [$username, $employee->email, '[DRY RUN] Would reset'];
                $successCount++;
            }
        }

        $this->table(['Username', 'Email', 'Status'], $rows);
        $this->newLine();

        if ($failCount > 0) {
            $this->error("Done. {$successCount} reset, {$failCount} failed.");

            return self::FAILURE;
        }

        $this->info("Done. All {$successCount} employee passwords reset successfully.");

        return self::SUCCESS;
    }
}
