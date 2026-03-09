<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * Restore database and media from a backup created by backup:run.
 */
class RestoreCommand extends Command
{
    protected $signature = 'backup:restore
        {path : Path to the backup directory (e.g. storage/backups/20250301-140000)}
        {--db-only : Only restore the database}
        {--media-only : Only restore media files}
        {--force : Skip confirmation prompts}';

    protected $description = 'Restore database and media from a backup archive';

    public function handle(): int
    {
        $backupPath = rtrim($this->argument('path'), '/\\');

        if (! is_dir($backupPath)) {
            $this->error("Backup directory not found: {$backupPath}");
            return self::FAILURE;
        }

        // Read manifest
        $manifestFile = $backupPath . '/manifest.json';
        if (File::exists($manifestFile)) {
            $manifest = json_decode(File::get($manifestFile), true);
            $this->info("Backup manifest:");
            $this->table(
                ['Key', 'Value'],
                collect($manifest)->except('files')->map(fn ($v, $k) => [$k, is_array($v) ? implode(', ', $v) : $v])->values()->toArray()
            );
            $this->newLine();
        }

        if (! $this->option('force')) {
            if (! $this->confirm('⚠  This will OVERWRITE current data. Continue?', false)) {
                $this->info('Restore cancelled.');
                return self::SUCCESS;
            }
        }

        $success = true;

        if (! $this->option('media-only')) {
            $success = $this->restoreDatabase($backupPath) && $success;
        }

        if (! $this->option('db-only')) {
            $success = $this->restoreMedia($backupPath) && $success;
        }

        $this->newLine();
        if ($success) {
            $this->info('✓ Restore complete.');
            $this->warn('  Run `php artisan cache:clear` to flush stale caches.');
        } else {
            $this->error('⚠  Restore completed with errors.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function restoreDatabase(string $backupPath): bool
    {
        $this->info('🗄  Restoring database...');

        // Find dump file
        $sqlFile = null;
        foreach (glob($backupPath . '/db-*.sql*') as $file) {
            $sqlFile = $file;
            break;
        }

        if (! $sqlFile) {
            $this->error("    No database dump found in: {$backupPath}");
            return false;
        }

        $host = config('database.connections.mariadb.host', '127.0.0.1');
        $port = config('database.connections.mariadb.port', '3306');
        $database = config('database.connections.mariadb.database', 'bebetter_store');
        $username = config('database.connections.mariadb.username', 'root');
        $password = config('database.connections.mariadb.password', '');

        $isGzipped = str_ends_with($sqlFile, '.gz');

        if ($isGzipped) {
            $cmd = sprintf(
                'gunzip -c %s | mysql --host=%s --port=%s --user=%s --password=%s %s',
                escapeshellarg($sqlFile),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database)
            );
        } else {
            $cmd = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($sqlFile)
            );
        }

        $result = Process::run($cmd);

        if ($result->successful()) {
            $this->info("    ✓ Database restored from: " . basename($sqlFile));
            return true;
        }

        $this->error("    ✗ Database restore failed: " . $result->errorOutput());
        return false;
    }

    private function restoreMedia(string $backupPath): bool
    {
        $this->info('🖼  Restoring media files...');

        $publicStorage = storage_path('app/public');

        // Check for compressed archive first
        $tarFile = null;
        foreach (glob($backupPath . '/media-*.tar.gz') as $file) {
            $tarFile = $file;
            break;
        }

        if ($tarFile) {
            File::ensureDirectoryExists($publicStorage, 0755, true);

            $result = Process::run(
                sprintf('tar -xzf %s -C %s', escapeshellarg($tarFile), escapeshellarg($publicStorage))
            );

            if ($result->successful()) {
                $this->info("    ✓ Media restored from: " . basename($tarFile));
                return true;
            }

            $this->error("    ✗ Media restore from tar failed: " . $result->errorOutput());
            return false;
        }

        // Check for uncompressed media directory
        $mediaDir = $backupPath . '/media';

        if (is_dir($mediaDir)) {
            try {
                File::copyDirectory($mediaDir, $publicStorage);
                $fileCount = count(File::allFiles($publicStorage));
                $this->info("    ✓ Media restored ({$fileCount} files)");
                return true;
            } catch (\Throwable $e) {
                $this->error("    ✗ Media restore failed: " . $e->getMessage());
                return false;
            }
        }

        $this->warn("    No media backup found in: {$backupPath}");
        return true;
    }
}

