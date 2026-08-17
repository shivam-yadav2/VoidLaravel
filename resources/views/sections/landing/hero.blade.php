<section class="hero-section hero-1 fix bg-cover" style="background-image: url({{ asset('assets/img/hero/hero-bg.jpg') }});">
  <div class="container">
    <div class="arrow-shape float-bob-x">
      <img src="{{ asset('assets/img/hero/arrow-icon.png') }}" alt="img" />
    </div>
    <div class="row">
      <div class="col-lg-12">
        <div class="hero-content">
          <h1 class="tx-title sec_title">
            <span class="hero-scan-line" id="hero-scan-line">
              <span class="hero-scan-line__base">Make your Product</span>
              <span class="hero-scan-pill" id="hero-scan-pill" aria-hidden="true">
                <span class="hero-scan-pill__text" id="hero-scan-pill-text">Make your Product</span>
              </span>
            </span>
            <br />
            <span class="hero-scan-static">stand out</span>
          </h1>
          <p class="hero-text wow fadeInUp" data-wow-delay=".3s">
            Void Layer helps businesses streamline operations through custom software, ERP &amp; HRMS systems, web applications and AI-powered automation — built by a small, senior team that stays on after launch.
          </p>
          <div class="hero-button-item">
            <a href="{{ url('/contact') }}" class="theme-btn wow fadeInUp" data-wow-delay=".5s">
              <img src="{{ asset('assets/img/button-arrow.svg') }}" alt="img" />
              Start a project
            </a>
          </div>
          <div class="hero-image wow fadeInUp" data-wow-delay=".7s"></div>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
  <script>
    (function () {
      function setup() {
        if (typeof gsap === "undefined") return;
        var line = document.getElementById("hero-scan-line");
        var pill = document.getElementById("hero-scan-pill");
        var pillText = document.getElementById("hero-scan-pill-text");
        if (!line || !pill || !pillText) return;

        if (setup._tween) setup._tween.kill();
        gsap.set(pill, { x: 0 });
        gsap.set(pillText, { x: 0 });

        var maxX = Math.max(line.offsetWidth - pill.offsetWidth, 0);
        setup._tween = gsap.to(pill, {
          x: maxX,
          duration: 2.6,
          ease: "sine.inOut",
          yoyo: true,
          repeat: -1,
          onUpdate: function () {
            gsap.set(pillText, { x: -gsap.getProperty(pill, "x") });
          },
        });
      }

      window.addEventListener("load", setup);
      window.addEventListener("resize", setup);
    })();
  </script>
@endpush
