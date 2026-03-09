<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $metaTitle ?? ($title ?? 'Homepage') }} · Be Better BSBL</title>
    <meta name="description" content="{{ $metaDescription ?? ($description ?? 'Be Better BSBL — Where Resilience Meets Lifestyle.') }}" />

    {{-- Canonical URL: explicit override or auto-generated from current path --}}
    @php
      $canonical = $canonicalUrl ?? url()->current();
    @endphp
    <link rel="canonical" href="{{ $canonical }}" />

    {{-- Noindex for drafts/unpublished products --}}
    @if(! empty($noindex))
      <meta name="robots" content="noindex, nofollow" />
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="/assets/css/main.css" />

    @stack('head')
  </head>

  <body class="{{ $bodyClass ?? '' }}">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.header')

    <main id="main" tabindex="-1" class="site-main">
      @yield('content')
    </main>

    @include('partials.footer')

    <script src="/assets/js/cart.js" defer></script>
    <script src="/assets/js/main.js" defer></script>
    <script src="/assets/js/search.js" defer></script>

    @stack('scripts')
  </body>
</html>
