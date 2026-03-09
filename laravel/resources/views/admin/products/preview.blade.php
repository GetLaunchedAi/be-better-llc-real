{{--
  Full-page storefront preview for admin.
  Re-uses the storefront PDP layout but adds an admin banner at the top.
--}}
@extends('layouts.app')

@push('head')
<style>
.admin-preview-bar {
    background: #0b416e;
    color: #fff;
    padding: 8px 16px;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    position: sticky;
    top: 0;
    z-index: 9999;
}
.admin-preview-bar a {
    color: #bae0fd;
    text-decoration: underline;
    font-weight: 600;
}
.admin-preview-bar a:hover { color: #fff; }
.admin-preview-bar .badge-status {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
}
.badge-status--active { background: #dcfce7; color: #166534; }
.badge-status--draft { background: #fef9c3; color: #854d0e; }
.badge-status--archived { background: #f3f4f6; color: #4b5563; }
</style>
@endpush

@section('content')
{{-- Admin banner --}}
<div class="admin-preview-bar">
    <div style="display:flex; align-items:center; gap:10px;">
        <strong>PREVIEW MODE</strong>
        <span class="badge-status badge-status--{{ $product->status }}">{{ ucfirst($product->status) }}</span>
        <span style="opacity:0.7;">{{ $product->title }}</span>
    </div>
    <div style="display:flex; align-items:center; gap:16px;">
        <a href="{{ route('admin.products.edit', $product) }}">← Back to editor</a>
        @if($product->status === 'active')
            <a href="/products/{{ $product->slug }}" target="_blank">View live ↗</a>
        @endif
    </div>
</div>

@php
  $primaryCollection = $product->primary_collection;
  $collectionLabels = [
      'men' => 'Men', 'women' => 'Women', 'youth' => 'Youth',
      'headwear' => 'Headwear', 'sale' => 'Sale', 'bags' => 'Bags', 'gear' => 'Gear',
  ];
  $collectionSlug = $primaryCollection ? $primaryCollection->slug : 'sale';
  $collectionLabel = $collectionLabels[$collectionSlug] ?? ucfirst($collectionSlug);
  $collectionHref = '/collections/' . $collectionSlug;

  $sizes = $product->distinct_sizes;
  $colors = $product->distinct_colors;
  if (empty($sizes)) $sizes = ['S','M','L','XL'];
  if (empty($colors)) $colors = ['Black','Navy','Stone'];

  $productImages = $product->images;
  $firstImg = $productImages->isNotEmpty()
      ? $productImages->first()->path
      : ($product->image ?? '/assets/img/placeholder.jpg');
@endphp

<section class="section pdp" data-product-page>
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <a class="breadcrumbs__link" href="/">Home</a>
      <span class="breadcrumbs__sep" aria-hidden="true">/</span>
      <a class="breadcrumbs__link" href="{{ $collectionHref }}">{{ $collectionLabel }}</a>
      <span class="breadcrumbs__sep" aria-hidden="true">/</span>
      <span class="breadcrumbs__current" aria-current="page">{{ $product->title }}</span>
    </nav>

    <div class="pdp__grid">
      {{-- Gallery --}}
      <div class="pdp-gallery" data-gallery>
        <div class="pdp-gallery__main">
          @if($productImages->isNotEmpty())
            <img class="pdp-gallery__img" src="{{ $firstImg }}" alt="{{ $product->title }}" loading="eager" data-gallery-img />
          @elseif($product->image)
            <img class="pdp-gallery__img" src="{{ $product->image }}" alt="{{ $product->title }}" loading="eager" data-gallery-img />
          @else
            <div class="pdp-gallery__placeholder" data-gallery-placeholder>
              <div class="pdp-gallery__ph-inner">
                <span class="pdp-gallery__ph-badge">Image</span>
                <span class="pdp-gallery__ph-label" data-placeholder-label>1</span>
              </div>
            </div>
          @endif
        </div>

        <div class="pdp-gallery__thumbs" aria-label="Gallery thumbnails">
          @if($productImages->isNotEmpty())
            @foreach($productImages as $img)
              <button class="thumb @if($loop->first) is-active @endif" type="button" data-gallery-thumb data-src="{{ $img->path }}" aria-label="View image {{ $loop->iteration }}">
                <img src="{{ $img->thumb_path ?? $img->path }}" alt="{{ $product->title }} thumbnail {{ $loop->iteration }}" loading="lazy" />
              </button>
            @endforeach
          @else
            @foreach([1,2,3] as $i)
              <button class="thumb thumb--placeholder @if($i === 1) is-active @endif" type="button" data-gallery-thumb data-placeholder="{{ $i }}" aria-label="View placeholder image {{ $i }}">
                <span class="thumb__ph">{{ $i }}</span>
              </button>
            @endforeach
          @endif
        </div>
      </div>

      {{-- Info --}}
      <div class="pdp-info">
        <p class="eyebrow">{{ $collectionLabel }}</p>
        <h1 class="pdp-title">{{ $product->title }}</h1>
        @if($product->subtitle)
          <p class="muted pdp-subtitle">{{ $product->subtitle }}</p>
        @endif

        <div class="pdp-meta">
          <div class="pdp-price" aria-label="Price">
            <span class="price-current">${{ $product->price }}</span>
            @if($product->compare_at)
              <span class="price-compare">${{ $product->compare_at }}</span>
            @endif
          </div>

          @if($product->rating)
            <div class="rating" aria-label="Rating">
              <span class="rating__stars" aria-hidden="true">★★★★★</span>
              <span class="rating__value">{{ $product->rating }}</span>
              @if($product->review_count)
                <span class="muted rating__count">({{ $product->review_count }})</span>
              @endif
            </div>
          @endif
        </div>

        <div class="pdp-selectors">
          <div class="selector">
            <p class="selector__label">Size</p>
            <div class="selector__row" role="list">
              @foreach($sizes as $s)
                <button class="option-btn" type="button" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $s }}</button>
              @endforeach
            </div>
          </div>

          <div class="selector">
            <p class="selector__label">Color</p>
            <div class="selector__row" role="list">
              @foreach($colors as $c)
                <button class="option-btn option-btn--chip" type="button" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $c }}</button>
              @endforeach
            </div>
          </div>
        </div>

        <div class="pdp-actions">
          <button class="btn btn-primary btn-lg" type="button" disabled style="opacity:0.7; cursor:not-allowed;">
            Add to cart (preview)
          </button>
          <p class="muted pdp-note">This is a preview. Cart and checkout are disabled.</p>
        </div>

        <div class="accordion">
          <details class="accordion__item" open>
            <summary class="accordion__summary">Shipping</summary>
            <div class="accordion__content">
              <p class="muted">Free shipping over $75. Standard delivery 3–5 business days.</p>
            </div>
          </details>
          <details class="accordion__item">
            <summary class="accordion__summary">Returns</summary>
            <div class="accordion__content">
              <p class="muted">Easy returns within 30 days. Items must be unworn with tags attached.</p>
            </div>
          </details>
          <details class="accordion__item">
            <summary class="accordion__summary">Details</summary>
            <div class="accordion__content">
              <p class="muted">{{ $product->details ?? 'No details available yet.' }}</p>
              @if($product->tags->isNotEmpty())
                <p class="muted"><strong>Tags:</strong> {{ $product->tags->pluck('name')->implode(', ') }}</p>
              @endif
            </div>
          </details>
        </div>
      </div>
    </div>

    <hr class="divider" />

    <section class="related">
      <div class="related__head">
        <h2 class="related__title">Related products</h2>
        <a class="muted related__link" href="{{ $collectionHref }}">Shop {{ $collectionLabel }}</a>
      </div>

      <div class="product-grid product-grid--compact">
        @foreach($related as $rp)
          @include('components.product-card', ['product' => $rp])
        @endforeach
      </div>
    </section>
  </div>
</section>
@endsection

