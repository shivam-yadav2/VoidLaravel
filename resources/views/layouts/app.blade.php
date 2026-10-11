@php
  // Every local CSS/JS file gets its own mtime as a cache-busting query
  // string: public/.htaccess gives these files long browser caching, so
  // without a changing URL an edited file would never reach browsers that
  // already cached the old one.
  $v = fn (string $path) => asset($path).'?v='.@filemtime(public_path($path));
@endphp
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#000000" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/void_logo.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" />
    @include('partials.seo')

    {{-- Brand fonts. Only the two families the theme actually uses (DM Sans
         for body copy, Unbounded for headings), as variable-weight ranges. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400..700;1,400..700&family=Unbounded:wght@300..800&display=swap" />

    <link rel="stylesheet" href="{{ $v('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ $v('assets/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ $v('assets/css/animate.css') }}" />
    <link rel="stylesheet" href="{{ $v('assets/css/meanmenu.css') }}" />
    <link rel="stylesheet" href="{{ $v('assets/css/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ $v('assets/css/main.css') }}" />
    <link rel="stylesheet" href="{{ $v('assets/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ $v('assets/css/blog.css') }}" />

    @stack('styles')

    @include('partials.tracking-head')
  </head>
  <body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="page-wrapper">
      @include('partials.header')

      <div id="smooth-wrapper">
        <div id="smooth-content">
          <main id="main-content">
            @yield('content')
          </main>

          @include('partials.support-cta')
        </div>
      </div>
    </div>

    <script src="{{ $v('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ $v('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ $v('assets/js/gsap.min.js') }}"></script>
    <script src="{{ $v('assets/js/ScrollTrigger.min.js') }}"></script>
    <script src="{{ $v('assets/js/ScrollSmoother.min.js') }}"></script>
    <script src="{{ $v('assets/js/SplitText.min.js') }}"></script>
    <script src="{{ $v('assets/js/jquery.waypoints.js') }}"></script>
    <script src="{{ $v('assets/js/jquery.counterup.min.js') }}"></script>
    <script src="{{ $v('assets/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ $v('assets/js/jquery.meanmenu.min.js') }}"></script>
    <script src="{{ $v('assets/js/wow.min.js') }}"></script>
    <script src="{{ $v('assets/js/main.js') }}"></script>

    @stack('scripts')

    @include('partials.tracking-body')
  </body>
</html>
