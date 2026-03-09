@extends('layouts.app')

@section('content')
<section class="section page">
  <div class="container" style="text-align:center; padding: 5rem 0;">
    <h1 class="page-title" style="font-size:3rem; margin-bottom:0.5rem;">404</h1>
    <p class="muted page-subtitle" style="font-size:1.1rem; margin-bottom:2rem;">
      Sorry, the page you're looking for doesn't exist or has been moved.
    </p>

    <div class="btn-row" style="justify-content:center; gap:1rem; flex-wrap:wrap;">
      <a class="btn btn-primary" href="/">Go home</a>
      <a class="btn btn-outline" href="/collections/men/">Shop Men</a>
      <a class="btn btn-outline" href="/collections/women/">Shop Women</a>
      <a class="btn btn-ghost" href="/search/">Search products</a>
    </div>

    <p class="muted" style="margin-top:3rem; font-size:0.85rem;">
      If you followed a link here, the product may have been removed. Try searching or browse our collections above.
    </p>
  </div>
</section>
@endsection

