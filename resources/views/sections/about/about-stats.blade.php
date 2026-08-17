@php
  $stats = \App\Models\Stat::active()->get();
@endphp
@if ($stats->isNotEmpty())
<div class="achievement-section section-bg">
  <div class="container">
    <div class="achievement-area">
      @foreach ($stats as $i => $stat)
        @if ($i > 0)
          <div class="line"></div>
        @endif
        <div class="achievement-icon-items wow fadeInUp" data-wow-delay="{{ min(0.3 + $i * 0.2, 1) }}s">
          <div class="icon">
            <img src="{{ $stat->iconUrl() ?: asset('assets/img/faq/icon-img-01.png') }}" alt="img" />
          </div>
          {{-- Deliberately not class="count" — main.js runs $(".count").counterUp()
               globally and expects a pure number; $stat->value can be free text
               like "1 day", which counterUp can't parse safely. --}}
          <h4>{{ $stat->value }}<br />{{ $stat->label }}</h4>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endif
