{{-- Consent banner — only when a cookie-setting tracker is configured (see
     partials/tracking-head). The choice is remembered in localStorage, so
     the banner shows once per browser. --}}
@if (preg_match('/^G-[A-Z0-9]{4,20}$/i', (string) $site->google_analytics_id) || preg_match('/^\d{5,20}$/', (string) $site->meta_pixel_id))
  <div class="consent-banner" id="consent-banner" role="region" aria-label="Cookie consent" hidden>
    <p class="mb-0">
      We&rsquo;d like to use analytics cookies to understand how visitors use this site.
      Nothing is tracked unless you accept. <a href="{{ route('page.show', 'privacy-policy') }}">Privacy policy</a>
    </p>
    <div class="consent-banner__actions">
      <button type="button" data-consent="granted">Accept</button>
      <button type="button" data-consent="denied">Decline</button>
    </div>
  </div>
  <script>
    (function () {
      var banner = document.getElementById("consent-banner");
      if (!banner || window.vlConsent) return;
      banner.hidden = false;
      banner.addEventListener("click", function (e) {
        var choice = e.target.getAttribute && e.target.getAttribute("data-consent");
        if (!choice) return;
        try { window.localStorage.setItem("vl-consent", choice); } catch (err) {}
        banner.hidden = true;
        if (choice === "granted" && window.vlLoadTrackers) window.vlLoadTrackers();
      });
    })();
  </script>
@endif

@if ($site->custom_body_code)
  {!! $site->custom_body_code !!}
@endif
