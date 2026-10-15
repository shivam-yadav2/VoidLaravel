{{-- Product grid + detail modal. Each product's detail markup is rendered
     server-side into an inert <template>, cloned into the modal on click —
     so the modal shows exactly the same content as /products/{slug}. --}}
<div class="product-grid">
  @foreach ($products as $product)
    @include('pages.products.card', ['product' => $product])
  @endforeach
</div>

@foreach ($products as $product)
  <template id="product-tpl-{{ $product->slug }}">
    @include('pages.products.detail', ['product' => $product, 'inModal' => true, 'idSuffix' => '-modal'])
  </template>
@endforeach

@once
  <div class="product-modal" data-product-modal hidden>
    <div class="product-modal__backdrop" data-product-close></div>
    <div class="product-modal__panel" role="dialog" aria-modal="true" data-product-panel tabindex="-1">
      <button type="button" class="product-modal__close" data-product-close aria-label="Close">
        <span aria-hidden="true"></span>
      </button>
      <div class="product-modal__body" data-product-body></div>
    </div>
  </div>

  @push('scripts')
    <script>
      (function () {
        var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        var hasGsap = typeof window.gsap !== "undefined" && !reduced;

        // ---------- Gallery (modal and standalone page) ----------
        window.vlProductGallery = function (root) {
          var stage = root.querySelector("[data-gallery-stage]");
          if (!stage) return { stop: function () {} };
          var slides = Array.prototype.slice.call(stage.querySelectorAll("[data-slide]"));
          var thumbs = Array.prototype.slice.call(root.querySelectorAll("[data-thumb]"));
          var count = root.querySelector("[data-gallery-count]");
          var index = 0;

          function stopMedia(slide) {
            var v = slide.querySelector("video");
            if (v) v.pause();
            var frame = slide.querySelector("iframe");
            if (frame) frame.remove(); // stops YouTube/Vimeo playback
            var embed = slide.querySelector("[data-embed-src]");
            if (embed) embed.classList.remove("is-playing");
          }

          function startEmbed(embed) {
            if (!embed || embed.querySelector("iframe")) return;
            var f = document.createElement("iframe");
            f.src = embed.getAttribute("data-embed-src") + "&autoplay=1";
            f.title = embed.getAttribute("data-embed-title") || "Video";
            f.allow = "autoplay; encrypted-media; fullscreen; picture-in-picture";
            f.allowFullscreen = true;
            embed.appendChild(f);
            embed.classList.add("is-playing");
          }

          function go(i) {
            if (!slides.length) return;
            i = (i + slides.length) % slides.length;
            if (i === index) return;
            stopMedia(slides[index]);
            slides[index].classList.remove("is-active");
            slides[index].setAttribute("aria-hidden", "true");
            slides[i].classList.add("is-active");
            slides[i].removeAttribute("aria-hidden");
            thumbs.forEach(function (t, n) {
              t.classList.toggle("is-active", n === i);
              t.setAttribute("aria-selected", n === i ? "true" : "false");
            });
            if (thumbs[i] && thumbs[i].scrollIntoView) thumbs[i].scrollIntoView({ block: "nearest", inline: "center", behavior: reduced ? "auto" : "smooth" });
            if (count) count.textContent = (i + 1) + " / " + slides.length;
            if (hasGsap) gsap.fromTo(slides[i], { autoAlpha: 0 }, { autoAlpha: 1, duration: 0.3, ease: "power1.out" });
            index = i;
          }

          root.addEventListener("click", function (e) {
            if (e.target.closest("[data-gallery-prev]")) go(index - 1);
            else if (e.target.closest("[data-gallery-next]")) go(index + 1);
            else if (e.target.closest("[data-thumb]")) go(+e.target.closest("[data-thumb]").getAttribute("data-thumb"));
            else if (e.target.closest("[data-embed-play]")) startEmbed(e.target.closest("[data-embed-src]"));
          });

          root.addEventListener("keydown", function (e) {
            if (e.target.closest("input, textarea, video, iframe")) return;
            if (e.key === "ArrowLeft") { e.preventDefault(); go(index - 1); }
            if (e.key === "ArrowRight") { e.preventDefault(); go(index + 1); }
          });

          // Swipe on touch screens.
          var startX = null;
          stage.addEventListener("pointerdown", function (e) { if (e.pointerType !== "mouse") startX = e.clientX; });
          stage.addEventListener("pointerup", function (e) {
            if (startX === null) return;
            var dx = e.clientX - startX; startX = null;
            if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
          });

          return { stop: function () { slides.forEach(stopMedia); } };
        };

        // Standalone detail pages.
        document.querySelectorAll("[data-gallery]").forEach(function (g) {
          if (!g.closest("template") && !g.closest("[data-product-modal]")) window.vlProductGallery(g);
        });

        // ---------- Modal ----------
        var modal = document.querySelector("[data-product-modal]");
        if (!modal) return;
        // ScrollSmoother moves page content with transforms, and a
        // position:fixed element inside a transformed parent stops being
        // fixed to the screen — so the modal lives directly under <body>.
        document.body.appendChild(modal);
        var panel = modal.querySelector("[data-product-panel]");
        var body = modal.querySelector("[data-product-body]");
        var gallery = null, opener = null, isOpen = false, closeTimer = null;
        // Matches the panel transition in custom.css (reduced motion: none).
        var duration = reduced ? 0 : 320;

        function lockScroll(lock) {
          var sm = window.ScrollSmoother && ScrollSmoother.get();
          if (sm) sm.paused(lock);
          document.documentElement.classList.toggle("modal-open", lock);
        }

        // Opening is a plain CSS transition (fade + small rise) on two
        // compositor-only properties — the old GSAP version scaled the whole
        // panel up from the card and staggered its text, which stuttered.
        function open(slug, card, pushHash) {
          var tpl = document.getElementById("product-tpl-" + slug);
          if (!tpl) return false;
          clearTimeout(closeTimer);
          opener = card || null;
          body.innerHTML = "";
          body.appendChild(tpl.content.cloneNode(true));
          var heading = body.querySelector(".pd-name");
          if (heading) panel.setAttribute("aria-labelledby", heading.id);
          modal.hidden = false;
          isOpen = true;
          lockScroll(true);
          gallery = window.vlProductGallery(body);
          body.scrollTop = 0;
          if (pushHash !== false) history.replaceState(null, "", "#product=" + slug);
          requestAnimationFrame(function () {
            requestAnimationFrame(function () { modal.classList.add("is-open"); });
          });
          panel.focus({ preventScroll: true });
          return true;
        }

        function close() {
          if (!isOpen) return;
          isOpen = false;
          if (gallery) gallery.stop();
          if (location.hash.indexOf("#product=") === 0) history.replaceState(null, "", location.pathname + location.search);
          modal.classList.remove("is-open");
          closeTimer = setTimeout(function () {
            modal.hidden = true;
            body.innerHTML = "";
            lockScroll(false);
            if (opener) opener.focus({ preventScroll: true });
          }, duration);
        }

        document.addEventListener("click", function (e) {
          var card = e.target.closest("[data-product-open]");
          if (card && !e.metaKey && !e.ctrlKey && !e.shiftKey && e.button === 0) {
            if (open(card.getAttribute("data-product-open"), card)) e.preventDefault();
          }
        });
        modal.addEventListener("click", function (e) { if (e.target.closest("[data-product-close]")) close(); });

        document.addEventListener("keydown", function (e) {
          if (!isOpen) return;
          if (e.key === "Escape") { e.preventDefault(); close(); return; }
          if (e.key === "Tab") {
            var f = Array.prototype.slice.call(panel.querySelectorAll('a[href], button:not([disabled]), video, iframe, [tabindex]:not([tabindex="-1"])'))
              .filter(function (el) { return el.offsetParent !== null; });
            if (!f.length) return;
            if (e.shiftKey && (document.activeElement === f[0] || document.activeElement === panel)) { e.preventDefault(); f[f.length - 1].focus(); }
            else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
          }
        });

        // Shared / bookmarked links: /products#product=slug opens that product.
        function fromHash() {
          var m = location.hash.match(/^#product=([a-z0-9-]+)$/);
          if (m && !isOpen) {
            var card = document.querySelector('[data-product-open="' + m[1] + '"]');
            open(m[1], card, false);
          } else if (!m && isOpen) {
            close();
          }
        }
        window.addEventListener("hashchange", fromHash);
        window.addEventListener("load", fromHash);
      })();
    </script>
  @endpush
@endonce
