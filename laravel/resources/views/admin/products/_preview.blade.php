{{-- Inline preview of the storefront PDP --}}
@php
    $primaryCollection = $product->primary_collection;
    $collectionLabels = [
        'men' => 'Men', 'women' => 'Women', 'youth' => 'Youth',
        'headwear' => 'Headwear', 'sale' => 'Sale', 'bags' => 'Bags', 'gear' => 'Gear',
    ];
    $collectionSlug = $primaryCollection ? $primaryCollection->slug : 'sale';
    $collectionLabel = $collectionLabels[$collectionSlug] ?? ucfirst($collectionSlug);

    $sizes = $product->distinct_sizes;
    $colors = $product->distinct_colors;
    if (empty($sizes)) $sizes = ['S','M','L','XL'];
    if (empty($colors)) $colors = ['Black','Navy','Stone'];

    $productImages = $product->images;
    $firstImg = $productImages->isNotEmpty()
        ? $productImages->first()->path
        : ($product->image ?? '/assets/img/placeholder.jpg');

    $statusColors = [
        'active' => 'bg-green-100 text-green-800 border-green-200',
        'draft' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'archived' => 'bg-gray-100 text-gray-600 border-gray-200',
    ];
@endphp

<div class="space-y-4">
    {{-- Status banner --}}
    <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold border {{ $statusColors[$product->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ ucfirst($product->status) }}
            </span>
            <span class="text-sm text-gray-500">
                This is how the product appears on the storefront.
                @if($product->status !== 'active')
                    <span class="text-yellow-700 font-medium">This product is not yet visible to customers.</span>
                @endif
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.preview', $product) }}" target="_blank"
               class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 border border-gray-300">
                Open full preview ↗
            </a>
            @if($product->status === 'active')
            <a href="/products/{{ $product->slug }}" target="_blank"
               class="rounded-md bg-brand-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-800">
                View live ↗
            </a>
            @endif
        </div>
    </div>

    {{-- Inline PDP preview --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-6" style="max-width: 900px; margin: 0 auto;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Gallery --}}
                <div>
                    <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                        @if($productImages->isNotEmpty())
                            <img src="{{ $firstImg }}" alt="{{ $product->title }}" class="w-full h-full object-cover" />
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-xs">No images uploaded</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($productImages->count() > 1)
                    <div class="flex gap-2 mt-3 overflow-x-auto">
                        @foreach($productImages->take(5) as $img)
                        <div class="w-16 h-16 flex-shrink-0 rounded border border-gray-200 overflow-hidden bg-gray-50">
                            <img src="{{ $img->thumb_path ?? $img->path }}" alt="" class="w-full h-full object-cover" />
                        </div>
                        @endforeach
                        @if($productImages->count() > 5)
                        <div class="w-16 h-16 flex-shrink-0 rounded border border-gray-200 bg-gray-50 flex items-center justify-center">
                            <span class="text-xs text-gray-400">+{{ $productImages->count() - 5 }}</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Product info --}}
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">{{ $collectionLabel }}</p>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ $product->title }}</h2>
                    @if($product->subtitle)
                        <p class="text-sm text-gray-500 mb-3">{{ $product->subtitle }}</p>
                    @endif

                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                        @if($product->compare_at)
                            <span class="text-sm text-gray-400 line-through">${{ number_format($product->compare_at, 2) }}</span>
                            @if($product->discount_percent)
                                <span class="text-xs font-semibold text-red-600 bg-red-50 px-1.5 py-0.5 rounded">{{ $product->discount_percent }}</span>
                            @endif
                        @endif
                    </div>

                    @if($product->rating)
                    <div class="flex items-center gap-1 mb-4">
                        <span class="text-yellow-400 text-sm">
                            @for($i = 1; $i <= 5; $i++){{ $product->rating >= $i ? '★' : '☆' }}@endfor
                        </span>
                        <span class="text-sm text-gray-600">{{ $product->rating }}</span>
                        @if($product->review_count)
                            <span class="text-xs text-gray-400">({{ $product->review_count }})</span>
                        @endif
                    </div>
                    @endif

                    {{-- Size chips --}}
                    @if(!empty($sizes))
                    <div class="mb-3">
                        <p class="text-xs font-medium text-gray-700 mb-1.5">Size</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($sizes as $s)
                                <span class="inline-flex items-center px-3 py-1.5 rounded border border-gray-300 text-xs font-medium text-gray-700 {{ $loop->first ? 'bg-gray-900 text-white border-gray-900' : '' }}">{{ $s }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Color chips --}}
                    @if(!empty($colors))
                    <div class="mb-4">
                        <p class="text-xs font-medium text-gray-700 mb-1.5">Color</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($colors as $c)
                                <span class="inline-flex items-center px-3 py-1.5 rounded border border-gray-300 text-xs font-medium text-gray-700 {{ $loop->first ? 'bg-gray-900 text-white border-gray-900' : '' }}">{{ $c }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <button type="button" disabled class="w-full rounded-md bg-gray-900 px-4 py-3 text-sm font-semibold text-white opacity-75 cursor-not-allowed">
                        Add to cart (preview)
                    </button>

                    @if($product->details)
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs font-medium text-gray-700 mb-1">Details</p>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $product->details }}</p>
                    </div>
                    @endif

                    @if($product->badge)
                    <div class="mt-3">
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 border border-brand-200">Badge: {{ $product->badge }}</span>
                    </div>
                    @endif

                    @if($product->tags->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach($product->tags as $tag)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- SEO preview --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">SEO Preview</h3>
        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
            <p class="text-blue-700 text-base font-medium truncate">
                {{ $product->meta_title ?: $product->title }} · Be Better BSBL
            </p>
            <p class="text-green-700 text-xs truncate mt-0.5">
                {{ url('/products/' . $product->slug) }}
            </p>
            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                {{ $product->meta_description ?: ($product->subtitle ?: 'No meta description set.') }}
            </p>
        </div>
        @if(empty($product->meta_title) || empty($product->meta_description))
        <p class="text-xs text-yellow-600 mt-2">
            ⚠ Missing SEO fields. Set meta title and description in the Details tab for better search rankings.
        </p>
        @endif
    </div>
</div>

