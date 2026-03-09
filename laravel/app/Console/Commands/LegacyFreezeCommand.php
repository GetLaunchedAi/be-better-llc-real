<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Freeze legacy writes during cutover.
 *
 * Creates a lock file that legacy PHP API endpoints can detect,
 * preventing any writes to products.json during the migration window.
 * Also puts the Laravel app into maintenance mode.
 */
class LegacyFreezeCommand extends Command
{
    protected $signature = 'cutover:freeze
        {--thaw : Remove the freeze (re-enable legacy writes)}
        {--legacy-root= : Path to legacy site root (defaults to ../src relative to laravel root)}';

    protected $description = 'Freeze legacy writes and put app in maintenance mode for cutover';

    public function handle(): int
    {
        $legacyRoot = $this->option('legacy-root')
            ?: base_path('../src');

        $lockFile = rtrim($legacyRoot, '/\\') . '/.migration-lock';

        if ($this->option('thaw')) {
            return $this->thaw($lockFile);
        }

        return $this->freeze($lockFile);
    }

    private function freeze(string $lockFile): int
    {
        // 1. Create lock file in legacy root
        $data = json_encode([
            'frozen_at' => now()->toIso8601String(),
            'reason' => 'Migration cutover in progress — legacy writes disabled.',
            'frozen_by' => get_current_user(),
        ], JSON_PRETTY_PRINT);

        File::ensureDirectoryExists(dirname($lockFile));
        File::put($lockFile, $data);

        $this->info("✓ Legacy lock file created: {$lockFile}");

        // 2. Create a read-only .htaccess override for legacy /api/ endpoints
        $apiDir = dirname($lockFile) . '/api';
        $htaccessPath = $apiDir . '/.htaccess-frozen';

        if (is_dir($apiDir)) {
            $htaccess = <<<'HTACCESS'
# FROZEN — Migration cutover active
# All write endpoints return 503 Service Unavailable
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^products-save\.php$ - [R=503,L]
    RewriteRule ^upload-image\.php$ - [R=503,L]
</IfModule>
ErrorDocument 503 '{"ok":false,"error":"Migration in progress. Writes are frozen."}'
HTACCESS;

            File::put($htaccessPath, $htaccess);
            $this->info("✓ Legacy API frozen .htaccess created: {$htaccessPath}");
        }

        // 3. Put Laravel into maintenance mode
        $this->call('down', [
            '--secret' => 'cutover-' . date('Ymd'),
            '--retry' => 60,
            '--render' => 'errors.503-cutover',
        ]);

        $this->newLine();
        $this->warn('⚠  LEGACY WRITES ARE NOW FROZEN');
        $this->info("   Secret bypass URL: /{cutover-" . date('Ymd') . "}");
        $this->info("   To thaw: php artisan cutover:freeze --thaw");

        return self::SUCCESS;
    }

    private function thaw(string $lockFile): int
    {
        // 1. Remove lock file
        if (File::exists($lockFile)) {
            File::delete($lockFile);
            $this->info("✓ Legacy lock file removed.");
        } else {
            $this->warn("No lock file found at: {$lockFile}");
        }

        // 2. Remove frozen .htaccess
        $htaccessPath = dirname($lockFile) . '/api/.htaccess-frozen';
        if (File::exists($htaccessPath)) {
            File::delete($htaccessPath);
            $this->info("✓ Legacy API frozen .htaccess removed.");
        }

        // 3. Bring Laravel back up
        $this->call('up');

        $this->newLine();
        $this->info('✓ System thawed — all writes re-enabled.');

        return self::SUCCESS;
    }
}

