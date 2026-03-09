@extends('layouts.app')

@section('content')
@php
  $primaryCollection = $product->primary_collection;
  $collectionLabels = [
      'men' => 'Men',
      'women' => 'Women',
      'youth' => 'Youth',
      'headwear' => 'Headwear',
      'sale' => 'Sale',
  ];
  $collectionSlug = $primaryCollection ? $primaryCollection->slug : 'sale';
  $collectionLabel = $collectionLabels[$collectionSlug] ?? ucfirst($collectionSlug);
  $collectionHref = '/collections/' . $collectionSlug . '/';

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
            <div class="selector__row" role="list" data-variant-group data-variant-name="size">
              @foreach($sizes as $s)
                <button class="option-btn" type="button" data-variant-option data-variant-value="{{ $s }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $s }}</button>
              @endforeach
            </div>
          </div>

          <div class="selector">
            <p class="selector__label">Color</p>
            <div class="selector__row" role="list" data-variant-group data-variant-name="color">
              @foreach($colors as $c)
                <button class="option-btn option-btn--chip" type="button" data-variant-option data-variant-value="{{ $c }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $c }}</button>
              @endforeach
            </div>
          </div>
        </div>

        <div class="pdp-actions">
          <button
            class="btn btn-primary btn-lg"
            type="button"
            data-cart-add
            data-id="{{ $product->legacy_id ?? $product->slug }}"
            data-slug="{{ $product->slug }}"
            data-title="{{ $product->title }}"
            data-price="{{ $product->price }}"
            data-size="{{ $sizes[0] ?? '' }}"
            data-color="{{ $colors[0] ?? '' }}"
            data-image="{{ $product->image ?? '/assets/img/placeholder.jpg' }}"
          >
            Add to cart
          </button>
          <p class="muted pdp-note">Saved in your cart (local). Checkout is a placeholder for now.</p>
        </div>

        <div class="accordion">
          <details class="accordion__item" open>
            <summary class="accordion__summary">Shipping</summary>
            <div class="accordion__content">
              <p class="muted">
                Free shipping over $75. Standard delivery 3–5 business days. Expedited options at checkout (wire later).
              </p>
            </div>
          </details>

          <details class="accordion__item">
            <summary class="accordion__summary">Returns</summary>
            <div class="accordion__content">
              <p class="muted">
                Easy returns within 30 days. Items must be unworn with tags attached. Start a return online (wire later).
              </p>
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

