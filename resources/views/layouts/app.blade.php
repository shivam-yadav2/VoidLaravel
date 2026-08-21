<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" href="{{ asset('assets/img/void_logo.png') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @include('partials.seo')

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/meanmenu.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    {{-- Cache-busted with the file's own mtime: public/.htaccess gives every
         CSS/JS file 30-day immutable browser caching, so without a changing
         query string, editing this file on the server would never reach
         browsers that already cached the old one. --}}
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v={{ @filemtime(public_path('assets/css/custom.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/blog.css') }}?v={{ @filemtime(public_path('assets/css/blog.css')) }}" />

    {{-- Only used by the video lightbox on the Services page (magnific-popup),
         triggered on click — never needed for first paint, so it loads without
         blocking render. noscript fallback covers JS-disabled visitors. --}}
    <link rel="preload" href="{{ asset('assets/css/magnific-popup.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript><link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}" /></noscript>

    @stack('styles')

    @include('partials.tracking-head')
  </head>
  <body>
    <div class="page-wrapper">
      @include('partials.offcanvas')
      <div class="offcanvas__overlay"></div>

      @include('partials.header')

      <div id="smooth-wrapper">
        <div id="smooth-content">
          @yield('content')

          @include('partials.support-cta')
        </div>
      </div>
    </div>

    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/viewport.jquery.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/gsap.min.js') }}"></script>
    <script src="{{ asset('assets/js/ScrollTrigger.min.js') }}"></script>
    <script src="{{ asset('assets/js/ScrollSmoother.min.js') }}"></script>
    <script src="{{ asset('assets/js/ScrollToPlugin.min.js') }}"></script>
    <script src="{{ asset('assets/js/SplitText.min.js') }}"></script>
    <script src="{{ asset('assets/js/TextPlugin.js') }}"></script>
    <script src="{{ asset('assets/js/chroma.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.waypoints.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('assets/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.meanmenu.min.js') }}"></script>
    <script src="{{ asset('assets/js/parallaxie.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('scripts')

    @include('partials.tracking-body')
  </body>
</html>
