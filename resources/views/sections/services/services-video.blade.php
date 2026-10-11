@php
  // Self-hosted studio reel (public/assets/video), played inside the laptop
  // artwork. Self-hosting keeps it within the site's Content-Security-Policy
  // (media-src 'self') and avoids YouTube's player and tracking cookies.
  $studioVideo = 'assets/video/void-layer-studio.mp4';
  $studioPoster = 'assets/video/void-layer-studio-poster.jpg';
  $hasStudioVideo = file_exists(public_path($studioVideo));
@endphp
<section class="tutorial-section section-padding fix bg-cover" style="background-image: url({{ asset('assets/img/feature/vedio-bg.webp') }});">
  <div class="container">
    <div class="section-title text-center">
      <span class="sub-title">
        <img loading="lazy" decoding="async" src="{{ asset('assets/img/title/icon-10.svg') }}" alt="" />
        Inside the studio
      </span>
      <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">See how we take products <br /> from idea to launch</h2>
    </div>

    <div class="studio-player" @if ($hasStudioVideo) data-studio-player @endif>
      <img class="studio-player__frame" loading="lazy" decoding="async"
           src="{{ asset('assets/img/feature/studio-laptop.webp') }}" width="1300" height="743"
           alt="{{ $hasStudioVideo ? '' : 'The Void Layer website on a laptop' }}" />

      @if ($hasStudioVideo)
        <div class="studio-player__screen">
          {{-- Muted + playsinline is what browsers require for autoplay; it
               starts when the section scrolls into view (script below). --}}
          <video muted loop playsinline preload="none"
                 @if (file_exists(public_path($studioPoster))) poster="{{ asset($studioPoster) }}" @endif
                 aria-label="Void Layer studio reel: how we take products from idea to launch">
            <source src="{{ asset($studioVideo) }}?v={{ @filemtime(public_path($studioVideo)) }}" type="video/mp4" />
          </video>

          <button type="button" class="studio-player__play" data-studio-play aria-label="Play video">
            <i class="fa-solid fa-play" aria-hidden="true"></i>
          </button>

          <div class="studio-player__controls">
            <button type="button" class="studio-player__btn" data-studio-sound aria-pressed="false" aria-label="Turn sound on">
              <i class="fa-solid fa-volume-xmark" aria-hidden="true"></i>
            </button>
            <button type="button" class="studio-player__btn" data-studio-fullscreen aria-label="Watch full screen">
              <i class="fa-solid fa-expand" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      @endif
    </div>
  </div>
</section>

@if ($hasStudioVideo)
  @push('scripts')
    <script>
      (function () {
        var player = document.querySelector("[data-studio-player]");
        if (!player) return;

        var video = player.querySelector("video");
        var playBtn = player.querySelector("[data-studio-play]");
        var soundBtn = player.querySelector("[data-studio-sound]");
        var fullBtn = player.querySelector("[data-studio-fullscreen]");
        var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        var userPaused = false;

        function play() {
          var p = video.play();
          if (p && p.catch) p.catch(function () {});
        }

        video.addEventListener("play", function () { player.classList.add("is-playing"); });
        video.addEventListener("pause", function () { player.classList.remove("is-playing"); });

        // Click the picture or the big button to play/pause.
        function toggle() {
          if (video.paused) { userPaused = false; play(); }
          else { userPaused = true; video.pause(); }
        }
        video.addEventListener("click", toggle);
        playBtn.addEventListener("click", toggle);

        soundBtn.addEventListener("click", function () {
          video.muted = !video.muted;
          if (!video.muted && video.paused) { userPaused = false; play(); }
          soundBtn.setAttribute("aria-pressed", String(!video.muted));
          soundBtn.setAttribute("aria-label", video.muted ? "Turn sound on" : "Turn sound off");
          soundBtn.querySelector("i").className = "fa-solid " + (video.muted ? "fa-volume-xmark" : "fa-volume-high");
        });

        fullBtn.addEventListener("click", function () {
          if (video.requestFullscreen) video.requestFullscreen();
          else if (video.webkitEnterFullscreen) video.webkitEnterFullscreen(); // iOS Safari
          if (video.paused) { userPaused = false; play(); }
        });

        // Autoplay (muted) while at least half the player is on screen, pause
        // when it leaves. Skipped for reduced-motion visitors (they get the
        // play button) and never overrides a pause the visitor chose.
        if (reducedMotion || !("IntersectionObserver" in window)) return;

        new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting && !userPaused) play();
            else if (!entry.isIntersecting && !video.paused) video.pause();
          });
        }, { threshold: 0.5 }).observe(player);
      })();
    </script>
  @endpush
@endif
