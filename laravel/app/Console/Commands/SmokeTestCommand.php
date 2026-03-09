<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Post-cutover smoke test — verifies critical URLs are responding
 * correctly from the new Laravel application.
 */
class SmokeTestCommand extends Command
{
    protected $signature = 'cutover:smoke-test
        {--base-url= : Base URL to test (defaults to APP_URL)}
        {--timeout=10 : HTTP request timeout in seconds}';

    protected $description = 'Run smoke tests against critical storefront URLs after cutover';

    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function handle(): int
    {
        $baseUrl = rtrim(
            $this->option('base-url') ?: config('app.url', 'http://localhost'),
            '/'
        );
        $timeout = (int) $this->option('timeout');

        $this->info("🔍 Running smoke tests against: {$baseUrl}");
        $this->info("   Timeout: {$timeout}s per request");
        $this->newLine();

        // ----------------------------------------------------------------
        // 1. Static / structural routes
        // ----------------------------------------------------------------
        $this->section('Static Routes');
        $this->check($baseUrl, '/', 200, 'Homepage');
        $this->check($baseUrl, '/search', 200, 'Search page');
        $this->check($baseUrl, '/search?q=hoodie', 200, 'Search with query');
        $this->check($baseUrl, '/admin/login', 200, 'Admin login page');

        // ----------------------------------------------------------------
        // 2. Product detail pages (sample from DB)
        // ----------------------------------------------------------------
        $this->section('Product Detail Pages');
        $products = Product::active()->limit(5)->get();

        if ($products->isEmpty()) {
            $this->warn('  ⚠  No active products in database — skipping PDP checks.');
        } else {
            foreach ($products as $product) {
                $this->check($baseUrl, '/products/' . $product->slug, 200, "PDP: {$product->title}");
            }
        }

        // ----------------------------------------------------------------
        // 3. Collection pages
        // ----------------------------------------------------------------
        $this->section('Collection Pages');
        $collections = Collection::limit(5)->get();

        if ($collections->isEmpty()) {
            $this->warn('  ⚠  No collections in database — skipping collection checks.');
        } else {
            foreach ($collections as $collection) {
                $this->check($baseUrl, '/collections/' . $collection->slug, 200, "Collection: {$collection->title}");
            }
        }

        // ----------------------------------------------------------------
        // 4. SEO / redirect behavior
        // ----------------------------------------------------------------
        $this->section('SEO & Redirect Behavior');

        // Trailing slash should 301 redirect
        if ($products->isNotEmpty()) {
            $slug = $products->first()->slug;
            $this->checkRedirect($baseUrl, "/products/{$slug}/", "/products/{$slug}", 'Trailing slash redirect (PDP)');
        }

        // Uppercase should redirect to lowercase
        $this->checkRedirect($baseUrl, '/Search', '/search', 'Uppercase redirect');

        // Non-existent product should 404
        $this->check($baseUrl, '/products/this-product-does-not-exist-xyz', 404, '404 for missing product');

        // ----------------------------------------------------------------
        // 5. Legacy endpoints should be gone
        // ----------------------------------------------------------------
        $this->section('Legacy Endpoint Retirement');
        $this->check($baseUrl, '/api/products-get.php', [404, 410], 'Legacy products-get.php → Gone/404');
        $this->check($baseUrl, '/api/products-save.php', [404, 410], 'Legacy products-save.php → Gone/404');
        $this->check($baseUrl, '/api/upload-image.php', [404, 410], 'Legacy upload-image.php → Gone/404');

        // ----------------------------------------------------------------
        // Results
        // ----------------------------------------------------------------
        $this->newLine();
        $this->line(str_repeat('─', 60));
        $this->info("  ✓ Passed: {$this->passed}");

        if ($this->failed > 0) {
            $this->error("  ✗ Failed: {$this->failed}");
            $this->newLine();

            foreach ($this->failures as $f) {
                $this->error("  FAIL: {$f['label']}");
                $this->line("        Expected: {$f['expected']}");
                $this->line("        Got:      {$f['actual']}");
            }

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('🎉 All smoke tests passed!');

        return self::SUCCESS;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("  <comment>{$title}</comment>");
        $this->line('  ' . str_repeat('─', strlen($title) + 4));
    }

    /**
     * @param int|array<int> $expectedStatus
     */
    private function check(string $baseUrl, string $path, int|array $expectedStatus, string $label): void
    {
        $url = $baseUrl . $path;
        $expectedStatuses = is_array($expectedStatus) ? $expectedStatus : [$expectedStatus];

        try {
            $response = Http::withOptions([
                'allow_redirects' => false,
                'timeout' => (int) $this->option('timeout'),
                'verify' => false,
            ])->get($url);

            $status = $response->status();

            if (in_array($status, $expectedStatuses)) {
                $this->line("    <info>✓</info> [{$status}] {$label}");
                $this->passed++;
            } else {
                $this->line("    <error>✗</error> [{$status}] {$label} (expected " . implode('|', $expectedStatuses) . ")");
                $this->failed++;
                $this->failures[] = [
                    'label' => $label,
                    'expected' => implode(' or ', $expectedStatuses),
                    'actual' => $status,
                ];
            }
        } catch (\Throwable $e) {
            $this->line("    <error>✗</error> [ERR] {$label}: " . Str::limit($e->getMessage(), 80));
            $this->failed++;
            $this->failures[] = [
                'label' => $label,
                'expected' => implode(' or ', $expectedStatuses),
                'actual' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }

    private function checkRedirect(string $baseUrl, string $from, string $expectedTo, string $label): void
    {
        $url = $baseUrl . $from;

        try {
            $response = Http::withOptions([
                'allow_redirects' => false,
                'timeout' => (int) $this->option('timeout'),
                'verify' => false,
            ])->get($url);

            $status = $response->status();
            $location = $response->header('Location') ?? '';

            // Normalize location for comparison
            $normalizedLocation = parse_url($location, PHP_URL_PATH) ?: $location;

            if ($status === 301 && $normalizedLocation === $expectedTo) {
                $this->line("    <info>✓</info> [301→{$expectedTo}] {$label}");
                $this->passed++;
            } elseif ($status === 301) {
                $this->line("    <error>✗</error> [301→{$normalizedLocation}] {$label} (expected →{$expectedTo})");
                $this->failed++;
                $this->failures[] = [
                    'label' => $label,
                    'expected' => "301 → {$expectedTo}",
                    'actual' => "301 → {$normalizedLocation}",
                ];
            } else {
                $this->line("    <error>✗</error> [{$status}] {$label} (expected 301 redirect)");
                $this->failed++;
                $this->failures[] = [
                    'label' => $label,
                    'expected' => "301 → {$expectedTo}",
                    'actual' => "HTTP {$status}",
                ];
            }
        } catch (\Throwable $e) {
            $this->line("    <error>✗</error> [ERR] {$label}: " . Str::limit($e->getMessage(), 80));
            $this->failed++;
            $this->failures[] = [
                'label' => $label,
                'expected' => "301 → {$expectedTo}",
                'actual' => 'Connection error',
            ];
        }
    }
}

