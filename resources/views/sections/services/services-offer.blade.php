{{-- "Our services" — the five things we sell, as an interactive list.
     Desktop: rows on the left, an animated illustration on the right that
     swaps with the active row; rows auto-advance (progress bar) until the
     visitor picks one. Phones: plain accordion, illustration above.
     Illustrations are pure HTML/CSS (no images) — see .svc-art in custom.css. --}}
@php
  $offers = [
    [
      'key' => 'web',
      'title' => 'Web Development',
      'lead' => 'Fast, search-friendly websites and web apps.',
      'text' => 'Business websites, customer portals and full web applications built on Laravel, React and Next.js — responsive, accessible and quick to load on every device.',
      'tags' => ['Business websites', 'Web portals', 'E-commerce', 'CMS & admin panels'],
    ],
    [
      'key' => 'app',
      'title' => 'App Development',
      'lead' => 'Mobile apps your customers actually keep.',
      'text' => 'Cross-platform Android and iOS apps with Flutter and React Native — offline-ready, with push notifications, payments and an admin dashboard behind them.',
      'tags' => ['Android & iOS', 'Flutter / React Native', 'Push & payments', 'Store launch'],
    ],
    [
      'key' => 'software',
      'title' => 'Custom Software Development',
      'lead' => 'Software shaped around your workflow.',
      'text' => 'ERP, CRM, HRMS and internal tools built around how your team really works — roles, approvals, reports and integrations with the systems you already use.',
      'tags' => ['ERP / CRM / HRMS', 'Workflow automation', 'Dashboards & reports', 'API integrations'],
    ],
    [
      'key' => 'marketing',
      'title' => 'Digital Marketing',
      'lead' => 'Campaigns that turn clicks into customers.',
      'text' => 'Google and Meta ad campaigns, social media content and landing pages — tracked end to end, so you can see what every rupee brings back.',
      'tags' => ['Google & Meta Ads', 'Social media', 'Landing pages', 'Conversion tracking'],
    ],
    [
      'key' => 'seo',
      'title' => 'SEO',
      'lead' => 'Get found by people already searching for you.',
      'text' => 'Technical audits, on-page optimisation, local SEO and content that ranks — with a clear monthly report on keywords, traffic and leads.',
      'tags' => ['Technical audit', 'On-page & content', 'Local SEO', 'Monthly reports'],
    ],
  ];
@endphp
<section class="svc-offer section-padding fix" id="our-services" data-svc>
  <div class="container">
    <div class="svc-offer__head">
      <div class="section-title mb-0">
        <span class="sub-title wow fadeInUp">
          <img loading="lazy" decoding="async" src="{{ asset('assets/img/title/icon-02.svg') }}" alt="" />
          Our services
        </span>
        <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">Everything you need to grow online</h2>
      </div>
      <p class="svc-offer__intro wow fadeInUp" data-wow-delay=".3s">
        From the first website to the software that runs your operations — and the marketing that brings customers in. One team, start to finish.
      </p>
    </div>

    <div class="svc-offer__grid">
      <ol class="svc-list">
        @foreach ($offers as $i => $offer)
          <li class="svc-item {{ $i === 0 ? 'is-active' : '' }}" data-svc-item>
            <button type="button" class="svc-item__head" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="svc-body-{{ $offer['key'] }}">
              <span class="svc-item__num">{{ sprintf('%02d', $i + 1) }}</span>
              <span class="svc-item__title">{{ $offer['title'] }}</span>
              <span class="svc-item__icon" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
            </button>
            <div class="svc-item__body" id="svc-body-{{ $offer['key'] }}">
              <div class="svc-item__inner">
                <p class="svc-item__lead">{{ $offer['lead'] }}</p>
                <p class="svc-item__text">{{ $offer['text'] }}</p>
                <ul class="svc-item__tags">
                  @foreach ($offer['tags'] as $tag)
                    <li>{{ $tag }}</li>
                  @endforeach
                </ul>
                <a href="{{ route('contact') }}" class="svc-item__cta">
                  Discuss your project <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
              </div>
            </div>
            <span class="svc-item__progress" aria-hidden="true"><span></span></span>
          </li>
        @endforeach
      </ol>

      <div class="svc-stage" aria-hidden="true">
        <div class="svc-stage__glow"></div>

        {{-- 01 Web: browser assembling a page while code types --}}
        <div class="svc-art svc-art--web is-active" data-svc-panel>
          <div class="art-window">
            <div class="art-window__bar"><i></i><i></i><i></i><span>yourbrand.com</span></div>
            <div class="art-window__body">
              <div class="art-code">
                <span style="--w: 62%; --d: .1s"></span>
                <span style="--w: 80%; --d: .3s" class="is-accent"></span>
                <span style="--w: 45%; --d: .5s"></span>
                <span style="--w: 70%; --d: .7s"></span>
                <span style="--w: 55%; --d: .9s" class="is-accent"></span>
                <span style="--w: 38%; --d: 1.1s"></span>
              </div>
              <div class="art-page">
                <div class="art-page__hero"><b></b><i></i><em></em></div>
                <div class="art-page__cards"><span></span><span></span><span></span></div>
              </div>
            </div>
          </div>
          <div class="art-chip art-chip--score">
            <span class="art-ring"><svg viewBox="0 0 36 36"><circle cx="18" cy="18" r="15.5"></circle><circle class="art-ring__fill" cx="18" cy="18" r="15.5"></circle></svg><b>100</b></span>
            <span>Performance</span>
          </div>
          <div class="art-chip art-chip--device"><i class="fa-solid fa-mobile-screen"></i><i class="fa-solid fa-tablet-screen-button"></i><i class="fa-solid fa-desktop"></i> Responsive</div>
        </div>

        {{-- 02 App: phone with sliding UI and a push notification --}}
        <div class="svc-art svc-art--app" data-svc-panel>
          <div class="art-phone">
            <div class="art-phone__notch"></div>
            <div class="art-phone__screen">
              <div class="art-phone__top"><span></span><i></i></div>
              <div class="art-phone__card"><small></small><b></b><span></span></div>
              <div class="art-phone__row" style="--d: .35s"><i></i><span></span><em></em></div>
              <div class="art-phone__row" style="--d: .5s"><i></i><span></span><em></em></div>
              <div class="art-phone__row" style="--d: .65s"><i></i><span></span><em></em></div>
              <div class="art-phone__tabs"><i></i><i></i><i></i><i></i></div>
            </div>
          </div>
          <div class="art-toast"><span class="art-toast__icon"><i class="fa-solid fa-bell"></i></span><span><b>Order confirmed</b><small>Your booking is ready</small></span><em>now</em></div>
          <div class="art-chip art-chip--stores"><i class="fa-brands fa-android"></i><i class="fa-brands fa-apple"></i> Android &amp; iOS</div>
        </div>

        {{-- 03 Custom software: dashboard + an approval workflow --}}
        <div class="svc-art svc-art--software" data-svc-panel>
          <div class="art-dash">
            <div class="art-dash__side"><b></b><i></i><i></i><i></i><i></i></div>
            <div class="art-dash__main">
              <div class="art-dash__kpis">
                <span style="--d: .1s"><small></small><b></b></span>
                <span style="--d: .2s"><small></small><b></b></span>
                <span style="--d: .3s"><small></small><b></b></span>
              </div>
              <div class="art-dash__table">
                <span style="--d: .4s"><i></i><em class="ok">Approved</em></span>
                <span style="--d: .5s"><i></i><em class="wait">Pending</em></span>
                <span style="--d: .6s"><i></i><em class="ok">Approved</em></span>
                <span style="--d: .7s"><i></i><em class="ok">Paid</em></span>
              </div>
            </div>
          </div>
          <div class="art-flow">
            <span style="--d: .8s"><i class="fa-solid fa-user-plus"></i>Lead</span>
            <b style="--d: 1s"></b>
            <span style="--d: 1.2s"><i class="fa-solid fa-check-double"></i>Approve</span>
            <b style="--d: 1.4s"></b>
            <span style="--d: 1.6s"><i class="fa-solid fa-file-invoice"></i>Invoice</span>
          </div>
        </div>

        {{-- 04 Marketing: rising campaign chart, funnel and a social post --}}
        <div class="svc-art svc-art--marketing" data-svc-panel>
          <div class="art-chart">
            <div class="art-chart__head"><span>Campaign results</span><em><i class="fa-solid fa-arrow-trend-up"></i> Leads</em></div>
            <div class="art-chart__bars">
              @foreach ([28, 40, 34, 52, 47, 66, 74, 88] as $n => $h)
                <span style="--h: {{ $h }}%; --d: {{ 0.1 + $n * 0.08 }}s"></span>
              @endforeach
            </div>
            <svg class="art-chart__line" viewBox="0 0 100 40" preserveAspectRatio="none"><path d="M0 34 L14 28 L28 30 L42 20 L57 23 L71 13 L85 9 L100 3"></path></svg>
          </div>
          <div class="art-funnel">
            <span style="--w: 100%; --d: .5s">Reach</span>
            <span style="--w: 76%; --d: .65s">Clicks</span>
            <span style="--w: 52%; --d: .8s">Leads</span>
          </div>
          <div class="art-post">
            <div class="art-post__head"><i></i><span></span></div>
            <div class="art-post__img"></div>
            <div class="art-post__foot"><i class="fa-solid fa-heart"></i><i class="fa-regular fa-comment"></i><i class="fa-regular fa-paper-plane"></i></div>
          </div>
        </div>

        {{-- 05 SEO: typed search, your result climbs to #1 --}}
        <div class="svc-art svc-art--seo" data-svc-panel>
          <div class="art-search"><i class="fa-solid fa-magnifying-glass"></i><span class="art-search__q">software company near me</span></div>
          <div class="art-results">
            <div class="art-result" style="--from: 0; --to: 1"><small>competitor-one.com</small><b></b><span></span></div>
            <div class="art-result" style="--from: 1; --to: 2"><small>directory-site.com</small><b></b><span></span></div>
            <div class="art-result" style="--from: 2; --to: 3"><small>another-agency.in</small><b></b><span></span></div>
            <div class="art-result is-you" style="--from: 3; --to: 0"><small>yourbrand.com <em>#1</em></small><b></b><span></span></div>
          </div>
          <div class="art-chip art-chip--rank"><i class="fa-solid fa-location-dot"></i> Top of local results</div>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
  <script>
    (function () {
      var root = document.querySelector("[data-svc]");
      if (!root) return;
      var items = Array.prototype.slice.call(root.querySelectorAll("[data-svc-item]"));
      var panels = Array.prototype.slice.call(root.querySelectorAll("[data-svc-panel]"));
      var list = root.querySelector(".svc-list");
      var grid = root.querySelector(".svc-offer__grid");
      var stage = root.querySelector(".svc-stage");
      var narrow = window.matchMedia("(max-width: 991px)");
      var index = 0;

      // Reduced motion or phones/tablets: no auto-advance (rows opening and
      // closing on their own while someone scrolls a phone is disorienting).
      if (window.matchMedia("(prefers-reduced-motion: reduce)").matches || narrow.matches) root.classList.add("is-manual");

      // Narrow screens: the illustration lives inside the open row, so it's
      // always next to what was tapped. Wide: it sits beside the list.
      function placeStage() {
        if (narrow.matches) {
          var inner = items[index].querySelector(".svc-item__inner");
          if (stage.parentNode !== inner) inner.insertBefore(stage, inner.firstChild);
        } else if (stage.parentNode !== grid) {
          grid.appendChild(stage);
        }
      }

      // Wide screens: reserve the tallest open state so the page below doesn't
      // shift each time a row with a longer description opens.
      function lockHeight() {
        if (narrow.matches) { list.style.minHeight = ""; return; }
        var heads = 0, body = 0;
        items.forEach(function (item) {
          heads += item.querySelector(".svc-item__head").offsetHeight + 1;
          body = Math.max(body, item.querySelector(".svc-item__inner").scrollHeight);
        });
        list.style.minHeight = (heads + body + 1) + "px";
      }

      function activate(i) {
        index = (i + items.length) % items.length;
        items.forEach(function (item, n) {
          var on = n === index;
          item.classList.toggle("is-active", on);
          item.querySelector(".svc-item__head").setAttribute("aria-expanded", on ? "true" : "false");
        });
        panels.forEach(function (panel, n) { panel.classList.toggle("is-active", n === index); });
        placeStage();
      }

      placeStage();
      lockHeight();
      window.addEventListener("load", lockHeight);
      window.addEventListener("resize", lockHeight);
      (narrow.addEventListener ? narrow.addEventListener.bind(narrow, "change") : narrow.addListener.bind(narrow))(function () {
        placeStage();
        lockHeight();
      });

      items.forEach(function (item, n) {
        var head = item.querySelector(".svc-item__head");
        head.addEventListener("click", function () {
          root.classList.add("is-manual"); // a deliberate pick stops autoplay
          activate(n);
        });
        // Desktop hover previews a row without stopping the rotation.
        head.addEventListener("mouseenter", function () {
          if (window.matchMedia("(hover: hover) and (min-width: 992px)").matches && n !== index) activate(n);
        });
        // The progress bar is a CSS animation; when it ends, move on.
        item.querySelector(".svc-item__progress span").addEventListener("animationend", function () {
          if (!root.classList.contains("is-manual") && item.classList.contains("is-active")) activate(index + 1);
        });
      });

      // Pause while the pointer is over the list, or the section is off screen.
      list.addEventListener("mouseenter", function () { root.classList.add("is-paused"); });
      list.addEventListener("mouseleave", function () { root.classList.remove("is-paused"); });
      if ("IntersectionObserver" in window) {
        new IntersectionObserver(function (entries) {
          root.classList.toggle("is-offscreen", !entries[0].isIntersecting);
        }, { threshold: 0.25 }).observe(root);
      }
    })();
  </script>
@endpush
