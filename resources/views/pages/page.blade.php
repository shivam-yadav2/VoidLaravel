@extends('layouts.app')

{{-- Generic admin-managed page (privacy policy, terms, …). Uses the same hero
     as About/Services/Contact so every page matches; the body adds a table of
     contents built from the page's own <h2>s, a reading-progress bar and
     scroll-reveal — all generated client-side, so admins just write content. --}}
@php
  $readMinutes = max(1, (int) ceil(str_word_count(strip_tags((string) $page->content)) / 200));
  $intro = $page->meta_description ?: null;
@endphp

@section('content')
  <div class="page-progress" aria-hidden="true"><span data-page-progress></span></div>

  <section class="hero-section hero-1 fix bg-cover page-hero" style="background-image: url({{ asset('assets/img/hero/hero-bg.webp') }});">
    <div class="container">
      <div class="arrow-shape float-bob-x">
        <img src="{{ asset('assets/img/hero/arrow-icon.png') }}" alt="" />
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="hero-content">
            <nav class="page-crumbs wow fadeInUp" aria-label="Breadcrumb">
              <a href="{{ route('home') }}">Home</a>
              <span aria-hidden="true">/</span>
              <span aria-current="page">{{ $page->title }}</span>
            </nav>
            <h1 class="tx-title sec_title">{{ $page->title }}</h1>
            @if ($intro)
              <p class="hero-text wow fadeInUp" data-wow-delay=".3s">{{ $intro }}</p>
            @endif
            <div class="page-meta wow fadeInUp" data-wow-delay=".45s">
              <span><i class="fa-regular fa-calendar" aria-hidden="true"></i>Last updated {{ $page->updated_at->displayTz()->format('d F Y') }}</span>
              <span><i class="fa-regular fa-clock" aria-hidden="true"></i>{{ $readMinutes }} min read</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="page-body">
    <div class="container">
      <div class="page-layout" data-page-layout>
        <aside class="page-toc" data-page-toc hidden>
          <p class="page-toc__label">On this page</p>
          <ol class="page-toc__list" data-page-toc-list></ol>
        </aside>

        <article class="page-article">
          <div class="prose page-prose" data-page-content>
            {{-- Authored by an authenticated admin, stored and rendered as HTML. --}}
            {!! $page->content !!}
          </div>

          <div class="page-cta" data-page-reveal>
            <div>
              <p class="page-cta__kicker">Questions about this page?</p>
              <p class="page-cta__text">Write to us at <a href="mailto:{{ $site->contact_email }}">{{ $site->contact_email }}</a> — we reply within one business day.</p>
            </div>
            <a href="{{ route('contact') }}" class="theme-btn">
              <img src="{{ asset('assets/img/button-arrow.svg') }}" alt="" />
              Contact us
            </a>
          </div>
        </article>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    // Runs as a jQuery ready handler registered after main.js's, so
    // ScrollSmoother already exists when the contents list gets pinned.
    (window.jQuery || function (fn) { document.addEventListener("DOMContentLoaded", fn); })(function () {
      var content = document.querySelector("[data-page-content]");
      if (!content) return;

      var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      var smoother = function () { return window.ScrollSmoother && ScrollSmoother.get(); };
      var HEADER_OFFSET = 130;

      // ---- Table of contents from the page's own <h2> headings ----
      var headings = Array.prototype.slice.call(content.querySelectorAll("h2"));
      var toc = document.querySelector("[data-page-toc]");
      var list = document.querySelector("[data-page-toc-list]");
      var used = {};
      var links = [];

      headings.forEach(function (h, i) {
        if (!h.id) {
          var base = (h.textContent || "section").toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, "").replace(/\s+/g, "-").slice(0, 60) || "section-" + (i + 1);
          var id = base, n = 2;
          while (used[id] || document.getElementById(id)) id = base + "-" + n++;
          h.id = id;
        }
        used[h.id] = true;

        var li = document.createElement("li");
        var a = document.createElement("a");
        a.href = "#" + h.id;
        a.textContent = h.textContent;
        a.addEventListener("click", function (e) {
          e.preventDefault();
          var sm = smoother();
          if (sm) sm.scrollTo(h, !reducedMotion, "top " + HEADER_OFFSET + "px");
          else window.scrollTo({ top: h.getBoundingClientRect().top + window.scrollY - HEADER_OFFSET, behavior: reducedMotion ? "auto" : "smooth" });
          history.replaceState(null, "", "#" + h.id);
        });
        li.appendChild(a);
        list.appendChild(li);
        links.push(a);
      });

      // Only worth showing for a page with real structure.
      if (headings.length >= 2) {
        toc.hidden = false;
        document.querySelector("[data-page-layout]").classList.add("has-toc");

        // Highlight the section currently being read.
        var setActive = function (id) {
          links.forEach(function (a) {
            var on = a.getAttribute("href") === "#" + id;
            a.classList.toggle("is-active", on);
            if (on) a.setAttribute("aria-current", "true"); else a.removeAttribute("aria-current");
          });
        };
        setActive(headings[0].id);
        if ("IntersectionObserver" in window) {
          var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) setActive(e.target.id); });
          }, { rootMargin: "-" + HEADER_OFFSET + "px 0px -60% 0px" });
          headings.forEach(function (h) { io.observe(h); });
        }

        // Keep the contents list in view while reading. ScrollSmoother moves
        // the page with transforms, which breaks CSS `position: sticky`, so
        // pin it with ScrollTrigger when smooth scrolling is active.
        if (smoother() && window.ScrollTrigger) {
          // Drop the CSS sticky fallback first — inside ScrollSmoother's
          // transformed wrapper it adds its own offset on top of the pin.
          document.querySelector("[data-page-layout]").classList.add("is-js-pinned");
          ScrollTrigger.matchMedia({
            "(min-width: 992px)": function () {
              ScrollTrigger.create({
                trigger: toc,
                pin: true,
                pinSpacing: false,
                start: "top " + HEADER_OFFSET + "px",
                endTrigger: content,
                end: "bottom " + (HEADER_OFFSET + toc.offsetHeight) + "px",
              });
            },
          });
        }
      }

      // ---- Reading progress (top of the screen) ----
      var bar = document.querySelector("[data-page-progress]");
      var ticking = false;
      function updateProgress() {
        ticking = false;
        var r = content.getBoundingClientRect();
        var total = r.height - window.innerHeight * 0.5;
        var done = Math.min(1, Math.max(0, (HEADER_OFFSET - r.top) / Math.max(total, 1)));
        bar.style.transform = "scaleX(" + done + ")";
      }
      window.addEventListener("scroll", function () {
        if (!ticking) { ticking = true; requestAnimationFrame(updateProgress); }
      }, { passive: true });
      window.addEventListener("resize", updateProgress);
      updateProgress();

      // ---- Scroll reveal for each block of the content ----
      if (reducedMotion || !("IntersectionObserver" in window)) return;
      var blocks = Array.prototype.slice.call(content.children)
        .concat(Array.prototype.slice.call(document.querySelectorAll("[data-page-reveal]")));
      var reveal = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) { e.target.classList.add("is-visible"); reveal.unobserve(e.target); }
        });
      }, { rootMargin: "0px 0px -8% 0px" });
      blocks.forEach(function (el) {
        // Anything already on screen at load shows immediately.
        if (el.getBoundingClientRect().top < window.innerHeight) return;
        el.classList.add("page-reveal");
        reveal.observe(el);
      });
    });
  </script>
@endpush
