{{--
  Admin-configured tracking snippets (Settings > Integrations). All optional —
  each block only renders once the matching key is filled in. Same trust model
  as blog/page content: only an authenticated admin can set these, so raw
  output is intentional, not an oversight.
--}}
@if ($site->google_analytics_id)
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $site->google_analytics_id }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ $site->google_analytics_id }}');
  </script>
@endif

@if ($site->meta_pixel_id)
  <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $site->meta_pixel_id }}');
    fbq('track', 'PageView');
  </script>
  <noscript>
    <img height="1" width="1" style="display:none" alt=""
         src="https://www.facebook.com/tr?id={{ $site->meta_pixel_id }}&ev=PageView&noscript=1" />
  </noscript>
@endif

@if ($site->custom_head_code)
  {!! $site->custom_head_code !!}
@endif
