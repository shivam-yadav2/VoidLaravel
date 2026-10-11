{{--
  Admin-configured tracking (Settings > Integrations). All optional — each
  block only renders once the matching key is filled in.

  Google Analytics and Meta Pixel set tracking cookies, so they only load
  after the visitor accepts the consent banner (partials/tracking-body). The
  IDs are format-checked before being written into JS, so a stray quote typed
  into the settings field can't break out of the string.

  Custom head code is NOT consent-gated: it's also used for things like
  search-console verification tags. Same trust model as blog/page content —
  only an authenticated admin can set it, so raw output is intentional.
--}}
@php
  $gaId = preg_match('/^G-[A-Z0-9]{4,20}$/i', (string) $site->google_analytics_id) ? strtoupper($site->google_analytics_id) : null;
  $pixelId = preg_match('/^\d{5,20}$/', (string) $site->meta_pixel_id) ? $site->meta_pixel_id : null;
@endphp

@if ($gaId || $pixelId)
  <script>
    (function () {
      var consent = null;
      try { consent = window.localStorage.getItem("vl-consent"); } catch (e) {}
      window.vlConsent = consent;

      window.vlLoadTrackers = function () {
        if (window.vlTrackersLoaded) return;
        window.vlTrackersLoaded = true;
        @if ($gaId)
          var ga = document.createElement("script");
          ga.async = true;
          ga.src = "https://www.googletagmanager.com/gtag/js?id={{ $gaId }}";
          document.head.appendChild(ga);
          window.dataLayer = window.dataLayer || [];
          window.gtag = function () { window.dataLayer.push(arguments); };
          window.gtag("js", new Date());
          window.gtag("config", "{{ $gaId }}");
        @endif
        @if ($pixelId)
          !function(f,b,e,v,n,t,s)
          {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
          n.callMethod.apply(n,arguments):n.queue.push(arguments)};
          if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
          n.queue=[];t=b.createElement(e);t.async=!0;
          t.src=v;s=b.getElementsByTagName(e)[0];
          s.parentNode.insertBefore(t,s)}(window, document,'script',
          'https://connect.facebook.net/en_US/fbevents.js');
          window.fbq('init', '{{ $pixelId }}');
          window.fbq('track', 'PageView');
        @endif
      };

      if (consent === "granted") window.vlLoadTrackers();
    })();
  </script>
@endif

@if ($site->custom_head_code)
  {!! $site->custom_head_code !!}
@endif
