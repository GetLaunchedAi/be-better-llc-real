<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\HomepageContentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\VariantController as AdminVariantController;
use App\Http\Controllers\Admin\ImageController as AdminImageController;
use App\Http\Controllers\Admin\BulkController as AdminBulkController;
use App\Http\Controllers\Admin\HomepageContentController as AdminHomepageContentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront Routes
|--------------------------------------------------------------------------
| Canonical trailing-slash redirects are handled globally by the
| CanonicalRedirect middleware, so only canonical (no-trailing-slash)
| routes are defined here.
*/

// Homepage
Route::get('/', function () {
    return view('home');
})->name('home');

// JSON Feed — /products.json
Route::get('/products.json', [ProductController::class, 'json'])->name('products.json');
Route::get('/homepage-content.json', [HomepageContentController::class, 'json'])->name('homepage-content.json');

// PDP — Product Detail Page
Route::get('/products/{slug}', [ProductController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('products.show');

// Collection (PLP)
Route::get('/collections/{slug}', [CollectionController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('collections.show');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Admin auth (public)
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin area (requires authentication + admin/editor role)
Route::prefix('admin')->middleware(['web', 'admin'])->name('admin.')->group(function () {

    // Dashboard redirect → products index
    Route::get('/', fn () => redirect()->route('admin.products.index'));

    // Products CRUD (read operations — admin + editor)
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::get('/products/{product}/preview', [AdminProductController::class, 'preview'])->name('products.preview');

    // Homepage marketing content editor
    Route::get('/homepage-content/edit', [AdminHomepageContentController::class, 'edit'])->name('homepage-content.edit');
    Route::put('/homepage-content', [AdminHomepageContentController::class, 'update'])->name('homepage-content.update');

    // Product duplication
    Route::post('/products/{product}/duplicate', [AdminProductController::class, 'duplicate'])->name('products.duplicate');

    // Destructive operations — admin role only
    Route::middleware('admin.role')->group(function () {
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        // Bulk operations
        Route::post('/bulk/status', [AdminBulkController::class, 'updateStatus'])->name('bulk.status');
        Route::post('/bulk/price', [AdminBulkController::class, 'updatePrice'])->name('bulk.price');
        Route::post('/bulk/delete', [AdminBulkController::class, 'destroy'])->name('bulk.delete');
    });

    // Variant management (nested under product)
    Route::post('/products/{product}/variants/generate', [AdminVariantController::class, 'generate'])->name('variants.generate');
    Route::put('/products/{product}/variants/{variant}', [AdminVariantController::class, 'update'])->name('variants.update');
    Route::post('/products/{product}/variants/bulk-toggle', [AdminVariantController::class, 'bulkToggle'])->name('variants.bulk-toggle');
    Route::post('/products/{product}/variants/bulk-price', [AdminVariantController::class, 'bulkPrice'])->name('variants.bulk-price');

    // Variant deletion — admin only
    Route::delete('/products/{product}/variants/{variant}', [AdminVariantController::class, 'destroy'])
        ->middleware('admin.role')
        ->name('variants.destroy');

    // Image management (nested under product)
    Route::post('/products/{product}/images', [AdminImageController::class, 'upload'])->name('images.upload');
    Route::put('/products/{product}/images/{image}', [AdminImageController::class, 'update'])->name('images.update');
    Route::post('/products/{product}/images/reorder', [AdminImageController::class, 'reorder'])->name('images.reorder');
    Route::post('/products/{product}/images/{image}/primary', [AdminImageController::class, 'setPrimary'])->name('images.primary');

    // Image deletion — admin only
    Route::delete('/products/{product}/images/{image}', [AdminImageController::class, 'destroy'])
        ->middleware('admin.role')
        ->name('images.destroy');
});
