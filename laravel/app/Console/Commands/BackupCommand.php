<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * Backup MariaDB database and media storage.
 *
 * Creates timestamped backup archives that can be used for disaster
 * recovery or pre-cutover snapshots.
 *
 * Prerequisites:
 *   - mysqldump binary available on the server
 *   - Sufficient disk space in the backup directory
 */
class BackupCommand extends Command
{
    protected $signature = 'backup:run
        {--db-only : Only backup the database}
        {--media-only : Only backup the media/storage files}
        {--dir= : Custom backup directory (defaults to storage/backups)}
        {--compress : Compress backups with gzip}';

    protected $description = 'Create a backup of the MariaDB database and product media';

    public function handle(): int
    {
        $backupDir = $this->option('dir')
            ?: storage_path('backups');

        $timestamp = now()->format('Ymd-His');
        $backupPath = rtrim($backupDir, '/\\') . '/' . $timestamp;

        File::ensureDirectoryExists($backupPath, 0755, true);

        $this->info("📦 Backup directory: {$backupPath}");
        $this->newLine();

        $success = true;

        if (! $this->option('media-only')) {
            $success = $this->backupDatabase($backupPath, $timestamp) && $success;
        }

        if (! $this->option('db-only')) {
            $success = $this->backupMedia($backupPath, $timestamp) && $success;
        }

        // Write backup manifest
        $this->writeManifest($backupPath, $timestamp);

        $this->newLine();
        if ($success) {
            $this->info("✓ Backup complete: {$backupPath}");
        } else {
            $this->error("⚠  Backup completed with errors. Check output above.");
            return self::FAILURE;
        }

        // Clean up old backups (keep last 5)
        $this->cleanOldBackups($backupDir, 5);

        return self::SUCCESS;
    }

    private function backupDatabase(string $backupPath, string $timestamp): bool
    {
        $this->info('🗄  Backing up database...');

        $host = config('database.connections.mariadb.host', '127.0.0.1');
        $port = config('database.connections.mariadb.port', '3306');
        $database = config('database.connections.mariadb.database', 'bebetter_store');
        $username = config('database.connections.mariadb.username', 'root');
        $password = config('database.connections.mariadb.password', '');

        $dumpFile = $backupPath . "/db-{$timestamp}.sql";

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database)
        );

        if ($this->option('compress')) {
            $dumpFile .= '.gz';
            $cmd .= ' | gzip';
        }

        $cmd .= ' > ' . escapeshellarg($dumpFile);

        $result = Process::run($cmd);

        if ($result->successful()) {
            $size = $this->humanFileSize(filesize($dumpFile));
            $this->info("    ✓ Database dump: {$dumpFile} ({$size})");
            return true;
        }

        $this->error("    ✗ Database dump failed: " . $result->errorOutput());

        // Fallback: try PHP-based dump via Artisan
        $this->warn("    Attempting PHP-based schema dump...");
        try {
            $this->call('schema:dump', ['--path' => $backupPath . "/schema-{$timestamp}.sql"]);
            return true;
        } catch (\Throwable $e) {
            $this->error("    ✗ Schema dump also failed: " . $e->getMessage());
            return false;
        }
    }

    private function backupMedia(string $backupPath, string $timestamp): bool
    {
        $this->info('🖼  Backing up media files...');

        $publicStorage = storage_path('app/public');

        if (! is_dir($publicStorage)) {
            $this->warn("    No public storage directory found — skipping media backup.");
            return true;
        }

        $mediaBackup = $backupPath . '/media';
        File::ensureDirectoryExists($mediaBackup);

        // Copy the entire public storage directory
        try {
            File::copyDirectory($publicStorage, $mediaBackup);

            $fileCount = count(File::allFiles($mediaBackup));
            $this->info("    ✓ Media backup: {$mediaBackup} ({$fileCount} files)");

            if ($this->option('compress')) {
                $tarFile = $backupPath . "/media-{$timestamp}.tar.gz";
                $result = Process::run(
                    sprintf('tar -czf %s -C %s .', escapeshellarg($tarFile), escapeshellarg($mediaBackup))
                );

                if ($result->successful()) {
                    File::deleteDirectory($mediaBackup);
                    $size = $this->humanFileSize(filesize($tarFile));
                    $this->info("    ✓ Compressed: {$tarFile} ({$size})");
                }
            }

            return true;
        } catch (\Throwable $e) {
            $this->error("    ✗ Media backup failed: " . $e->getMessage());
            return false;
        }
    }

    private function writeManifest(string $backupPath, string $timestamp): void
    {
        $manifest = [
            'timestamp' => $timestamp,
            'created_at' => now()->toIso8601String(),
            'app_version' => config('app.name') . ' (Laravel ' . app()->version() . ')',
            'database' => config('database.connections.mariadb.database'),
            'php_version' => PHP_VERSION,
            'server' => gethostname(),
            'files' => array_map(
                fn ($f) => basename($f),
                glob($backupPath . '/*')
            ),
        ];

        File::put(
            $backupPath . '/manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function cleanOldBackups(string $backupDir, int $keep): void
    {
        if (! is_dir($backupDir)) {
            return;
        }

        $dirs = array_filter(glob($backupDir . '/*'), 'is_dir');
        rsort($dirs); // newest first

        $toDelete = array_slice($dirs, $keep);

        foreach ($toDelete as $dir) {
            File::deleteDirectory($dir);
            $this->line("    🗑  Removed old backup: " . basename($dir));
        }
    }

    private function humanFileSize(int|false $bytes): string
    {
        if ($bytes === false || $bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $factor), 2) . ' ' . $units[(int) $factor];
    }
}

