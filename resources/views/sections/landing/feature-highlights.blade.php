{{-- Tech badges: a slow marquee of evenly sized cards. The list is rendered
     twice so the -50% scroll loops seamlessly; the second copy is hidden
     from screen readers (and from everyone under reduced motion). --}}
@if ($badges->isNotEmpty())
<section class="tech-strip" aria-labelledby="tech-strip-label">
  <p class="tech-strip__label" id="tech-strip-label">Technologies we work with</p>
  <div class="tech-strip__viewport">
    <div class="tech-strip__track" style="--tech-duration: {{ max(30, $badges->count() * 6) }}s">
      @foreach ([false, true] as $isCopy)
        <ul class="tech-strip__list" @if ($isCopy) aria-hidden="true" @endif>
          @foreach ($badges as $badge)
            @php [$faIcon, $faColor] = $badge->fallbackIcon(); @endphp
            <li class="tech-card">
              <span class="tech-card__icon" style="background: {{ $faColor }}1a; color: {{ $faColor }}" aria-hidden="true">
                @if ($badge->iconUrl())
                  <img src="{{ $badge->iconUrl() }}" alt="" width="34" height="34" loading="lazy" decoding="async" />
                @else
                  <i class="{{ $faIcon }}"></i>
                @endif
              </span>
              <span>
                <span class="tech-card__title d-block">{{ $badge->title }}</span>
                @if ($badge->subtitle)
                  <span class="tech-card__subtitle d-block">{{ $badge->subtitle }}</span>
                @endif
              </span>
            </li>
          @endforeach
        </ul>
      @endforeach
    </div>
  </div>
</section>
@endif
