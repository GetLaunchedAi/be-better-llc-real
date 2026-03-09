@extends('layouts.app')

@section('content')
<section class="section page">
  <div class="container">
    <header class="page-head" style="text-align:center; padding: 4rem 0;">
      <h1 class="page-title">BE BETTER. BSBL.</h1>
      <p class="muted page-subtitle">Where Resilience Meets Lifestyle.</p>
    </header>

    <div class="btn-row" style="justify-content:center; gap:1rem; flex-wrap:wrap;">
      <a class="btn btn-primary" href="/collections/men/">Shop Men</a>
      <a class="btn btn-primary" href="/collections/women/">Shop Women</a>
      <a class="btn btn-primary" href="/collections/youth/">Shop Youth</a>
      <a class="btn btn-outline" href="/collections/headwear/">Headwear</a>
      <a class="btn btn-outline" href="/collections/sale/">Sale</a>
    </div>
  </div>
</section>
@endsection

