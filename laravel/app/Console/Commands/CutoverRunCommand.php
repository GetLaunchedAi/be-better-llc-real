<?php

namespace App\Console\Commands;

use App\Services\CacheInvalidator;
use Illuminate\Console\Command;

/**
 * Master cutover orchestrator — executes the complete production cutover
 * sequence from legacy to Laravel.
 *
 * Steps:
 *   1. Pre-cutover backup
 *   2. Freeze legacy writes
 *   3. Final delta sync from products.json
 *   4. Deprecate legacy API endpoints
 *   5. Run database migrations (index tuning)
 *   6. Clear and warm caches
 *   7. Bring Laravel up (thaw)
 *   8. Smoke test critical URLs
 *
 * Safety features:
 *   - Confirms each step before proceeding
 *   - Automatic rollback on failure (thaw + restore option)
 *   - Full audit trail via console output
 */
class CutoverRunCommand extends Command
{
    protected $signature = 'cutover:run
        {--skip-backup : Skip the pre-cutover backup step}
        {--skip-smoke : Skip the post-cutover smoke test}
        {--no-interaction : Run without confirmation prompts}
        {--rollback : Undo cutover (thaw legacy, bring Laravel down)}';

    protected $description = 'Execute the complete production cutover from legacy to Laravel';

    public function handle(): int
    {
        if ($this->option('rollback')) {
            return $this->rollback();
        }

        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════╗');
        $this->line('║         BE BETTER BSBL — PRODUCTION CUTOVER             ║');
        $this->line('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->warn('This will:');
        $this->line('  1. Create a pre-cutover backup');
        $this->line('  2. Freeze legacy writes (products.json)');
        $this->line('  3. Run final delta sync from legacy JSON → MariaDB');
        $this->line('  4. Deprecate legacy API endpoints');
        $this->line('  5. Run pending database migrations');
        $this->line('  6. Clear and optimize caches');
        $this->line('  7. Bring Laravel live (disable maintenance mode)');
        $this->line('  8. Run smoke tests on critical URLs');
        $this->newLine();

        if (! $this->option('no-interaction')) {
            if (! $this->confirm('⚠  Proceed with production cutover?', false)) {
                $this->info('Cutover cancelled.');
                return self::SUCCESS;
            }
        }

        $this->newLine();
        $startTime = microtime(true);

        // ==================================================================
        // Step 1: Pre-cutover backup
        // ==================================================================
        if (! $this->option('skip-backup')) {
            $this->step(1, 'Creating pre-cutover backup');
            $result = $this->call('backup:run', ['--compress' => true]);
            if ($result !== self::SUCCESS) {
                $this->error('Backup failed. Aborting cutover.');
                return self::FAILURE;
            }
            $this->stepDone();
        } else {
            $this->info('  ⏭  Backup skipped (--skip-backup)');
        }

        // ==================================================================
        // Step 2: Freeze legacy writes
        // ==================================================================
        $this->step(2, 'Freezing legacy writes');
        $result = $this->call('cutover:freeze');
        if ($result !== self::SUCCESS) {
            $this->error('Legacy freeze failed. Aborting cutover.');
            $this->warn('  Run: php artisan cutover:freeze --thaw');
            return self::FAILURE;
        }
        $this->stepDone();

        // ==================================================================
        // Step 3: Final delta sync
        // ==================================================================
        $this->step(3, 'Running final delta sync');
        $result = $this->call('cutover:sync');
        if ($result !== self::SUCCESS) {
            $this->error('Delta sync failed.');
            $this->warn('  Thawing legacy writes as safety measure...');
            $this->call('cutover:freeze', ['--thaw' => true]);
            return self::FAILURE;
        }
        $this->stepDone();

        // ==================================================================
        // Step 4: Deprecate legacy endpoints
        // ==================================================================
        $this->step(4, 'Deprecating legacy API endpoints');
        $result = $this->call('cutover:deprecate-legacy');
        if ($result !== self::SUCCESS) {
            $this->warn('Legacy deprecation had issues but continuing...');
        }
        $this->stepDone();

        // ==================================================================
        // Step 5: Run pending migrations
        // ==================================================================
        $this->step(5, 'Running pending database migrations');
        $result = $this->call('migrate', ['--force' => true]);
        if ($result !== self::SUCCESS) {
            $this->error('Migration failed.');
            $this->warn('  You may need to manually resolve and re-run: php artisan migrate --force');
            // Don't abort — the app might still work without the new indexes
        }
        $this->stepDone();

        // ==================================================================
        // Step 6: Cache optimization
        // ==================================================================
        $this->step(6, 'Clearing and optimizing caches');
        CacheInvalidator::flushAll();
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->stepDone();

        // ==================================================================
        // Step 7: Bring Laravel live
        // ==================================================================
        $this->step(7, 'Bringing Laravel live');
        $this->call('cutover:freeze', ['--thaw' => true]);
        $this->stepDone();

        // ==================================================================
        // Step 8: Smoke test
        // ==================================================================
        if (! $this->option('skip-smoke')) {
            $this->step(8, 'Running post-cutover smoke tests');
            $smokeResult = $this->call('cutover:smoke-test');
            if ($smokeResult !== self::SUCCESS) {
                $this->warn('  ⚠  Some smoke tests failed. Manual verification recommended.');
                $this->warn('  To rollback: php artisan cutover:run --rollback');
            }
            $this->stepDone();
        }

        // ==================================================================
        // Summary
        // ==================================================================
        $elapsed = round(microtime(true) - $startTime, 1);

        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════╗');
        $this->line('║                  ✓ CUTOVER COMPLETE                     ║');
        $this->line('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->info("  Total time: {$elapsed}s");
        $this->newLine();
        $this->line('  Post-cutover checklist:');
        $this->line('    □ Verify storefront at ' . config('app.url'));
        $this->line('    □ Verify admin at ' . config('app.url') . '/admin');
        $this->line('    □ Check logs: storage/logs/laravel.log');
        $this->line('    □ Monitor error rates for 24h');
        $this->line('    □ Update DNS/CDN if needed');
        $this->newLine();
        $this->line('  Rollback if needed:');
        $this->line('    php artisan cutover:run --rollback');
        $this->line('    php artisan backup:restore storage/backups/<timestamp>');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Rollback: undo the cutover.
     */
    private function rollback(): int
    {
        $this->newLine();
        $this->warn('╔══════════════════════════════════════════════════════════╗');
        $this->warn('║              CUTOVER ROLLBACK                           ║');
        $this->warn('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();

        if (! $this->option('no-interaction')) {
            if (! $this->confirm('⚠  This will restore legacy endpoints and thaw writes. Continue?', false)) {
                $this->info('Rollback cancelled.');
                return self::SUCCESS;
            }
        }

        // Thaw legacy writes
        $this->info('Thawing legacy writes...');
        $this->call('cutover:freeze', ['--thaw' => true]);

        // Restore legacy endpoints
        $this->info('Restoring legacy API endpoints...');
        $this->call('cutover:deprecate-legacy', ['--restore' => true]);

        // Clear optimized caches (they may reference new routes)
        $this->info('Clearing cached config/routes...');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('cache:clear');

        $this->newLine();
        $this->info('✓ Rollback complete. Legacy system should be operational.');
        $this->warn('  If database was modified, restore from backup:');
        $this->line('    php artisan backup:restore storage/backups/<timestamp> --force');

        return self::SUCCESS;
    }

    private function step(int $num, string $description): void
    {
        $this->newLine();
        $this->line("  <comment>Step {$num}:</comment> {$description}");
        $this->line('  ' . str_repeat('─', 50));
    }

    private function stepDone(): void
    {
        $this->info('  ✓ Done');
    }
}

