<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CollectionController extends Controller
{
    /**
     * PLP — Product Listing Page (Collection).
     * Cached per collection+sort+filter combination.
     */
    public function show(Request $request, string $slug)
    {
        $sort = $request->get('sort', 'featured');
        $filter = $request->get('filter');
        $price = $request->get('price');

        $ttl = config('cache.ttl.collection_show', 300);
        $cacheKey = "collection:{$slug}:s={$sort}:f={$filter}:p={$price}";

        $data = Cache::remember($cacheKey, $ttl, function () use ($slug, $sort, $filter, $price) {
            $collection = Collection::where('slug', $slug)->firstOrFail();

            $query = $collection->products()->active()->with(['images', 'collections', 'tags']);

            // Sorting
            $query = match ($sort) {
                'price-asc'  => $query->orderBy('price', 'asc'),
                'price-desc' => $query->orderBy('price', 'desc'),
                'title-asc'  => $query->orderBy('title', 'asc'),
                'title-desc' => $query->orderBy('title', 'desc'),
                default       => $query->orderBy('products.id', 'asc'), // featured = insertion order
            };

            // Simple filter support
            if ($filter === 'new') {
                $query->where(function ($q) {
                    $q->where('badge', 'like', '%New%')
                      ->orWhereHas('tags', fn ($t) => $t->where('name', 'new'));
                });
            } elseif ($filter === 'sale') {
                $query->whereNotNull('compare_at')->where('compare_at', '>', 0);
            } elseif ($filter === 'best') {
                $query->where('badge', 'like', '%Best%');
            }

            // Price range filter
            if ($price === '0-25') {
                $query->where('price', '<=', 25);
            } elseif ($price === '25-75') {
                $query->whereBetween('price', [25, 75]);
            } elseif ($price === '75+') {
                $query->where('price', '>=', 75);
            }

            $products = $query->get();

            // SEO
            $metaTitle = $collection->title . ' Collection';
            $metaDescription = $collection->description ?: ($collection->title . ' — Shop the collection at Be Better BSBL.');
            $canonicalUrl = url('/collections/' . $collection->slug);

            return [
                'collection'      => $collection,
                'products'        => $products,
                'metaTitle'       => $metaTitle,
                'metaDescription' => $metaDescription,
                'canonicalUrl'    => $canonicalUrl,
            ];
        });

        return view('collections.show', array_merge($data, [
            'sort'   => $sort,
            'filter' => $filter,
            'price'  => $price,
        ]));
    }
}
