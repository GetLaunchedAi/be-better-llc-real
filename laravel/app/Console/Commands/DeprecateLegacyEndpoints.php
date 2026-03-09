<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Replace legacy PHP API endpoints with deprecation stubs.
 *
 * After cutover, the old /api/*.php files should return deprecation
 * notices pointing consumers to the new Laravel admin interface.
 */
class DeprecateLegacyEndpoints extends Command
{
    protected $signature = 'cutover:deprecate-legacy
        {--legacy-root= : Path to legacy site root (defaults to ../src)}
        {--restore : Restore original files from backups}';

    protected $description = 'Replace legacy PHP API files with deprecation stubs';

    private array $endpoints = [
        'api/products-get.php',
        'api/products-save.php',
        'api/upload-image.php',
        'api/login.php',
        'api/logout.php',
        'api/me.php',
    ];

    public function handle(): int
    {
        $legacyRoot = $this->option('legacy-root')
            ?: base_path('../src');

        $legacyRoot = rtrim($legacyRoot, '/\\');

        if ($this->option('restore')) {
            return $this->restore($legacyRoot);
        }

        return $this->deprecate($legacyRoot);
    }

    private function deprecate(string $legacyRoot): int
    {
        $this->info('Deprecating legacy API endpoints...');
        $this->newLine();

        foreach ($this->endpoints as $relative) {
            $file = $legacyRoot . '/' . $relative;

            if (! File::exists($file)) {
                $this->warn("  ⏭  Not found: {$relative}");
                continue;
            }

            // Backup original
            $backup = $file . '.bak-' . date('Ymd-His');
            File::copy($file, $backup);
            $this->line("  📦 Backed up: {$relative} → " . basename($backup));

            // Write deprecation stub
            $stub = $this->generateStub($relative);
            File::put($file, $stub);
            $this->info("  ✓ Deprecated: {$relative}");
        }

        $this->newLine();
        $this->info('✓ All legacy endpoints deprecated.');
        $this->warn('  Consumers will receive 410 Gone with migration notice.');

        return self::SUCCESS;
    }

    private function restore(string $legacyRoot): int
    {
        $this->info('Restoring legacy API endpoints from backups...');
        $restoredCount = 0;

        foreach ($this->endpoints as $relative) {
            $file = $legacyRoot . '/' . $relative;
            $dir = dirname($file);

            // Find most recent backup
            $pattern = $file . '.bak-*';
            $backups = glob($pattern);

            if (empty($backups)) {
                $this->warn("  No backup found for: {$relative}");
                continue;
            }

            sort($backups);
            $latest = end($backups);

            File::copy($latest, $file);
            $this->info("  ✓ Restored: {$relative} from " . basename($latest));
            $restoredCount++;
        }

        $this->newLine();
        $this->info("✓ Restored {$restoredCount} endpoint(s).");

        return self::SUCCESS;
    }

    private function generateStub(string $endpoint): string
    {
        $name = basename($endpoint, '.php');

        return <<<PHP
<?php
/**
 * DEPRECATED — This endpoint has been retired.
 *
 * The Be Better BSBL storefront has migrated to Laravel.
 * All product management is now handled via the admin panel at /admin.
 *
 * Original endpoint: {$endpoint}
 * Deprecated on: {$this->now()}
 */

header('Content-Type: application/json; charset=utf-8');
http_response_code(410); // 410 Gone

echo json_encode([
    'ok' => false,
    'error' => 'This endpoint has been permanently retired.',
    'migration' => [
        'message' => 'The Be Better BSBL storefront now uses Laravel. Use the admin panel at /admin for product management.',
        'admin_url' => '/admin',
        'deprecated_on' => '{$this->now()}',
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
exit;
PHP;
    }

    private function now(): string
    {
        return now()->toIso8601String();
    }
}

