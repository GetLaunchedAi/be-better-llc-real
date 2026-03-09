@extends('layouts.app')

@section('content')
@php
  $key = $collection->slug;
@endphp

<section class="collection-hero section">
  <div class="container">
    <div class="collection-hero__inner">
      <p class="eyebrow">Collection</p>
      <h1 class="collection-hero__title">{{ $collection->title }}</h1>
      @if($collection->description)
        <p class="collection-hero__subtitle muted">{{ $collection->description }}</p>
      @endif
    </div>
  </div>
</section>

<div class="plp__filters-backdrop" data-filters-backdrop hidden></div>

<section class="section section-tight">
  <div class="container">
    <div class="plp" data-collection-page data-collection-key="{{ $key }}">
      <div class="plp__mobilebar">
        <button class="btn btn-ghost btn-sm" type="button" data-filters-toggle>
          Filters
        </button>

        <div class="plp__sort" style="display:flex; gap:10px; align-items:center;">
          <label class="sr-only" for="sort-select">Sort</label>
          <select id="sort-select" class="select" data-sort-select>
            <option value="featured" @if(($sort ?? 'featured') === 'featured') selected @endif>Sort: Featured</option>
            <option value="price-asc" @if(($sort ?? '') === 'price-asc') selected @endif>Price: Low → High</option>
            <option value="price-desc" @if(($sort ?? '') === 'price-desc') selected @endif>Price: High → Low</option>
            <option value="title-asc" @if(($sort ?? '') === 'title-asc') selected @endif>Name: A → Z</option>
            <option value="title-desc" @if(($sort ?? '') === 'title-desc') selected @endif>Name: Z → A</option>
          </select>

          <button class="btn btn-ghost btn-sm" type="button" data-products-refresh>Refresh</button>
        </div>
      </div>

      <aside class="plp__sidebar" aria-label="Filters">
        <div class="card">
          <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
            <h2 class="card__title">Filters</h2>
            <button class="btn btn-ghost btn-sm" type="button" data-filters-close>Close</button>
          </div>
          <p class="muted card__text">Tap a chip to filter products. Filters update the URL so you can share the view.</p>

          <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top: 10px;">
            <button class="btn btn-outline btn-sm" type="button" data-filters-clear>Clear</button>
          </div>

          <div class="filter-group">
            <p class="filter-label">Popular</p>
            <div class="chip-row">
              <button class="chip" type="button" data-filter-chip data-filter-type="filter" data-filter-value="new" aria-pressed="{{ ($filter ?? '') === 'new' ? 'true' : 'false' }}">New</button>
              <button class="chip" type="button" data-filter-chip data-filter-type="filter" data-filter-value="best" aria-pressed="{{ ($filter ?? '') === 'best' ? 'true' : 'false' }}">Best Sellers</button>
              <button class="chip" type="button" data-filter-chip data-filter-type="filter" data-filter-value="sale" aria-pressed="{{ ($filter ?? '') === 'sale' ? 'true' : 'false' }}">On Sale</button>
            </div>
          </div>

          <div class="filter-group">
            <p class="filter-label">Price</p>
            <div class="chip-row">
              <button class="chip" type="button" data-filter-chip data-filter-type="price" data-filter-value="0-25" aria-pressed="{{ ($price ?? '') === '0-25' ? 'true' : 'false' }}">$0–$25</button>
              <button class="chip" type="button" data-filter-chip data-filter-type="price" data-filter-value="25-75" aria-pressed="{{ ($price ?? '') === '25-75' ? 'true' : 'false' }}">$25–$75</button>
              <button class="chip" type="button" data-filter-chip data-filter-type="price" data-filter-value="75+" aria-pressed="{{ ($price ?? '') === '75+' ? 'true' : 'false' }}">$75+</button>
            </div>
          </div>
        </div>
      </aside>

      <div class="plp__main">
        <div class="plp__toolbar">
          <p class="muted plp__count" data-plp-count>
            @if($products->count())
              Showing {{ $products->count() }} item{{ $products->count() !== 1 ? 's' : '' }}
            @else
              No products found
            @endif
          </p>

          <div style="display:flex; gap:10px; align-items:center;">
            <button class="btn btn-ghost btn-sm" type="button" data-products-refresh>Refresh</button>

            <div class="plp__sort plp__sort--desktop">
              <label class="muted" for="sort-select-desktop">Sort</label>
              <select id="sort-select-desktop" class="select" data-sort-select>
                <option value="featured" @if(($sort ?? 'featured') === 'featured') selected @endif>Featured</option>
                <option value="price-asc" @if(($sort ?? '') === 'price-asc') selected @endif>Price: Low → High</option>
                <option value="price-desc" @if(($sort ?? '') === 'price-desc') selected @endif>Price: High → Low</option>
                <option value="title-asc" @if(($sort ?? '') === 'title-asc') selected @endif>Name: A → Z</option>
                <option value="title-desc" @if(($sort ?? '') === 'title-desc') selected @endif>Name: Z → A</option>
              </select>
            </div>
          </div>
        </div>

        <div class="product-grid" data-product-grid>
          @foreach($products as $product)
            @include('components.product-card', ['product' => $product])
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

