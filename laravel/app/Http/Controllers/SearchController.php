<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    /**
     * Search page — server-side filtering.
     * Results cached per query term for short TTL.
     */
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $products = collect();

        if (strlen($q) >= 2) {
            $ttl = config('cache.ttl.search_results', 120);
            $cacheKey = 'search:' . md5(strtolower($q));

            $products = Cache::remember($cacheKey, $ttl, function () use ($q) {
                return Product::active()
                    ->search($q)
                    ->with(['images', 'collections', 'tags'])
                    ->limit(50)
                    ->get();
            });
        }

        return view('search.index', [
            'query'           => $q,
            'products'        => $products,
            'metaTitle'       => $q ? "Search: {$q}" : 'Search',
            'metaDescription' => 'Search Be Better BSBL products by name, collection, or tag.',
            'noindex'         => true, // Search pages should not be indexed
        ]);
    }
}
