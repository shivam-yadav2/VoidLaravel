@php
  $badges = \App\Models\TechBadge::active()->get();
@endphp
@if ($badges->isNotEmpty())
<div class="feature-highlights-section section-bg">
  <div class="feature-wrapper">
    @for ($row = 0; $row < 3; $row++)
      <div class="feature-items">
        @foreach ($badges as $i => $badge)
          <div class="feature-box {{ $i % 2 === 0 ? 'style-2' : '' }}">
            <div class="icon">
              <img src="{{ $badge->iconUrl() ?: asset('assets/img/core-feature/icon-0'.(($i % 8) + 1).'.png') }}" alt="img" />
            </div>
            <h4>{{ $badge->title }}</h4>
            @if ($badge->subtitle)
              <span class="box-style">{{ $badge->subtitle }}</span>
            @endif
          </div>
        @endforeach
      </div>
    @endfor
  </div>
</div>
@endif
