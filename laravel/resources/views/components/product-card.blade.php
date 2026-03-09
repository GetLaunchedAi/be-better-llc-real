{{--
  Product card component
  Usage: @include('components.product-card', ['product' => $product, 'variant' => 'default'])
--}}
@php
  $variant = $variant ?? 'default';
  $url = $product->url ?? '#';
  $image = $product->image ?? '/assets/img/placeholder.jpg';
  $badge = $product->badge ?? null;
  $discountPct = $product->discount_percent;
@endphp

<article class="product-card" data-variant="{{ $variant }}" data-title="{{ $product->title }}" data-price="{{ $product->price }}">
  <a class="product-card__link" href="{{ $url }}">
    <div class="product-card__media">
      <img class="product-card__img" src="{{ $image }}" alt="{{ $product->title }}" loading="lazy" decoding="async" />

      @if($badge)
        <div class="product-card__badges" aria-label="Product badge">
          @php
            $label = $badge;
            $l = strtolower($label);
            $cls = 'badge--neutral';
            if (str_contains($l, 'sale')) $cls = 'badge--sale';
            elseif (str_contains($l, 'new')) $cls = 'badge--new';
            elseif (str_contains($l, 'best')) $cls = 'badge--best';
          @endphp
          <span class="badge {{ $cls }}">{{ $label }}</span>
        </div>
      @endif
    </div>

    <div class="product-card__body">
      <h3 class="product-card__title">{{ $product->title }}</h3>

      @if($product->subtitle)
        <p class="product-card__meta">{{ $product->subtitle }}</p>
      @endif

      @if($product->rating)
        <div class="product-card__rating" aria-label="Rating">
          <span class="rating__stars" aria-hidden="true">
            @for($i = 1; $i <= 5; $i++)
              {{ $product->rating >= $i ? '★' : '☆' }}
            @endfor
          </span>
          <span class="rating__value">{{ $product->rating }}</span>
          @if($product->review_count)
            <span class="rating__count">{{ $product->review_count }}</span>
          @endif
        </div>
      @endif

      <div class="product-card__price" aria-label="Price">
        <span class="price-current">${{ $product->price }}</span>
        @if($product->compare_at)
          <span class="price-compare">${{ $product->compare_at }}</span>
        @endif
        @if($discountPct)
          <span class="price-discount">{{ $discountPct }}</span>
        @endif
      </div>
    </div>
  </a>

  <div class="product-card__actions">
    @if($variant === 'giveaway' && $product->giveaway_entries)
      <span class="btn btn-outline btn-sm btn-entries" aria-hidden="true">{{ $product->giveaway_entries }} GIVEAWAY ENTRIES</span>
    @else
      <button
        class="btn btn-outline btn-sm product-card__quick"
        type="button"
        data-cart-add
        data-id="{{ $product->legacy_id ?? $product->slug }}"
        data-slug="{{ $product->slug }}"
        data-title="{{ $product->title }}"
        data-price="{{ $product->price }}"
        data-image="{{ $image }}"
        aria-label="Add {{ $product->title }} to cart"
      >
        Add to cart
      </button>
    @endif
  </div>
</article>

