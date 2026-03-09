@extends('layouts.app')

@section('content')
<section class="section page">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <a class="breadcrumbs__link" href="/">Home</a>
      <span class="breadcrumbs__sep" aria-hidden="true">/</span>
      <span class="breadcrumbs__current" aria-current="page">Search</span>
    </nav>

    <header class="page-head">
      <h1 class="page-title">Search</h1>
      <p class="muted page-subtitle">Find products by name, collection, or tag.</p>
    </header>

    <div class="page-content prose">
      <div class="search" data-search-page>
        <form class="search-bar" role="search" aria-label="Search products" method="get" action="{{ route('search') }}">
          <label class="sr-only" for="search-input">Search products</label>
          <input id="search-input" class="input" type="search" name="q" placeholder="Search products…" autocomplete="off" value="{{ $query }}" data-search-input />
          <button class="btn btn-ghost" type="submit">Search</button>
        </form>

        @if($query)
          <p class="muted search-meta" data-search-meta>
            @if($products->count())
              {{ $products->count() }} result{{ $products->count() !== 1 ? 's' : '' }} for "{{ $query }}"
            @else
              No results for "{{ $query }}"
            @endif
          </p>
        @else
          <p class="muted search-meta" data-search-meta>Type to start searching.</p>
        @endif

        @if($products->isNotEmpty())
          <div class="product-grid" data-search-results aria-live="polite">
            @foreach($products as $product)
              @include('components.product-card', ['product' => $product])
            @endforeach
          </div>
        @elseif($query)
          <div class="search-empty card">
            <h2 class="card-title">No matches</h2>
            <p class="muted">Try a shorter query or browse a collection.</p>
            <div class="btn-row">
              <a class="btn btn-primary" href="/collections/sale/">Shop Sale</a>
              <a class="btn btn-ghost" href="/collections/men/">Men</a>
              <a class="btn btn-ghost" href="/collections/women/">Women</a>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection

