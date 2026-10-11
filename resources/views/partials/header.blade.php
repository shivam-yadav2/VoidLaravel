@php
  // Single source for the main menu: config/site.php → nav.
  $menuLinks = config('site.nav');
@endphp

{{-- Fixed header. mix-blend-mode: difference (see custom.css) inverts the
     white logo/icon against whatever is behind it, so it stays readable over
     the dark heroes, white sections and the lime marquee alike. --}}
<header class="site-header" id="site-header">
  <a href="{{ route('home') }}" class="site-header__logo" aria-label="{{ $site->site_name }} — home">void layer</a>

  <button type="button" class="site-header__toggle" data-menu-toggle
          aria-expanded="false" aria-controls="site-menu" aria-label="Open menu">
    <span class="site-header__plus" aria-hidden="true"><span></span><span></span></span>
  </button>
</header>

{{-- Full-screen menu, opened by the plus button and animated by the script
     at the bottom of this file. Hidden from everyone until opened. --}}
<div class="site-menu" id="site-menu" data-menu hidden role="dialog" aria-modal="true" aria-label="Site menu">
  <div class="site-menu__glow" aria-hidden="true"></div>

  <div class="site-menu__inner">
    <nav class="site-menu__nav" aria-label="Main">
      <ol class="site-menu__links">
        @foreach ($menuLinks as $i => [$route, $match, $label])
          @php $current = request()->routeIs($match); @endphp
          <li>
            <a href="{{ route($route) }}" class="site-menu__link {{ $current ? 'is-current' : '' }}" @if ($current) aria-current="page" @endif>
              <span class="site-menu__num" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
              <span class="site-menu__mask"><span class="site-menu__label" data-menu-label>{{ $label }}</span></span>
            </a>
          </li>
        @endforeach
      </ol>
    </nav>

    <div class="site-menu__aside" data-menu-aside>
      <p class="site-menu__kicker">Get in touch</p>
      <a href="mailto:{{ $site->contact_email }}" class="site-menu__email">{{ $site->contact_email }}</a>
      @if ($site->has('contact_phone'))
        <a href="tel:{{ $site->contact_phone_link }}" class="site-menu__line">{{ $site->contact_phone }}</a>
      @endif
      <p class="site-menu__line">{{ $site->contact_address }}</p>

      @php
        $menuSocialLabels = [
          'fa-brands fa-instagram' => 'Instagram',
          'fa-brands fa-linkedin-in' => 'LinkedIn',
          'fa-brands fa-whatsapp' => 'WhatsApp',
          'fa-brands fa-facebook-f' => 'Facebook',
          'fa-brands fa-x-twitter' => 'X (Twitter)',
        ];
      @endphp
      <div class="site-menu__social">
        @foreach ($site->socialLinks() as $icon => $url)
          <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $menuSocialLabels[$icon] ?? 'Social link' }}"><i class="{{ $icon }}" aria-hidden="true"></i></a>
        @endforeach
      </div>

      <a href="{{ route('contact') }}" class="theme-btn site-menu__cta">
        <img src="{{ asset('assets/img/button-arrow.svg') }}" alt="" />
        Start a project
      </a>
    </div>
  </div>
</div>

@push('scripts')
  <script>
    (function () {
      var toggle = document.querySelector("[data-menu-toggle]");
      var menu = document.querySelector("[data-menu]");
      if (!toggle || !menu) return;

      var header = document.getElementById("site-header");
      var labels = menu.querySelectorAll("[data-menu-label]");
      var nums = menu.querySelectorAll(".site-menu__num");
      var aside = menu.querySelector("[data-menu-aside]");
      var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      var hasGsap = typeof window.gsap !== "undefined" && !reduced;
      var isOpen = false, tl = null;

      // The menu grows out of the plus button as a circle.
      function origin() {
        var r = toggle.getBoundingClientRect();
        return Math.round(r.left + r.width / 2) + "px " + Math.round(r.top + r.height / 2) + "px";
      }

      function setScrollLock(lock) {
        var sm = window.ScrollSmoother && ScrollSmoother.get();
        if (sm) sm.paused(lock);
        document.documentElement.classList.toggle("menu-open", lock);
      }

      function open() {
        if (isOpen) return;
        isOpen = true;
        menu.hidden = false;
        header.classList.add("is-open");
        toggle.setAttribute("aria-expanded", "true");
        toggle.setAttribute("aria-label", "Close menu");
        setScrollLock(true);

        if (hasGsap) {
          if (tl) tl.kill();
          var at = origin();
          tl = gsap.timeline();
          tl.fromTo(menu, { clipPath: "circle(0% at " + at + ")" }, { clipPath: "circle(150% at " + at + ")", duration: 0.8, ease: "power3.inOut" })
            .fromTo(labels, { yPercent: 110 }, { yPercent: 0, duration: 0.7, ease: "power3.out", stagger: 0.06 }, 0.3)
            .fromTo(nums, { autoAlpha: 0 }, { autoAlpha: 1, duration: 0.4, stagger: 0.06 }, 0.45)
            .fromTo(aside, { autoAlpha: 0, y: 30 }, { autoAlpha: 1, y: 0, duration: 0.6, ease: "power3.out" }, 0.5);
        }

        var first = menu.querySelector(".site-menu__link");
        if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, hasGsap ? 350 : 0);
      }

      function close(returnFocus) {
        if (!isOpen) return;
        isOpen = false;
        header.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-label", "Open menu");

        var done = function () {
          menu.hidden = true;
          setScrollLock(false);
          if (returnFocus) toggle.focus({ preventScroll: true });
        };

        if (hasGsap) {
          if (tl) tl.kill();
          var at = origin();
          tl = gsap.timeline({ onComplete: done });
          tl.to(labels, { yPercent: -110, duration: 0.35, ease: "power2.in", stagger: 0.03 })
            .to(aside, { autoAlpha: 0, duration: 0.25 }, 0)
            .to(menu, { clipPath: "circle(0% at " + at + ")", duration: 0.6, ease: "power3.inOut" }, 0.15);
        } else {
          done();
        }
      }

      toggle.addEventListener("click", function () { isOpen ? close(true) : open(); });

      // Navigating closes the menu (same-page links too).
      menu.addEventListener("click", function (e) {
        if (e.target.closest("a")) close(false);
      });

      document.addEventListener("keydown", function (e) {
        if (!isOpen) return;
        if (e.key === "Escape") { e.preventDefault(); close(true); return; }

        // Keep keyboard focus inside the open menu (plus the close button).
        if (e.key === "Tab") {
          var items = [toggle].concat(Array.prototype.slice.call(menu.querySelectorAll("a[href], button")));
          var idx = items.indexOf(document.activeElement);
          if (e.shiftKey && idx <= 0) { e.preventDefault(); items[items.length - 1].focus(); }
          else if (!e.shiftKey && idx === items.length - 1) { e.preventDefault(); items[0].focus(); }
        }
      });
    })();
  </script>
@endpush
