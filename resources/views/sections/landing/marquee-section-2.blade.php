@php
  $texts = [' 280 EXPERT TRAINERS', ' Creative UI Solutions', ' Next-Gen Interfaces'];
@endphp
<div class="marquee-section-2">
  <div class="marquee-2">
    @for ($groupIdx = 0; $groupIdx < 3; $groupIdx++)
      <div class="marquee-group">
        @for ($rep = 0; $rep < 2; $rep++)
          @foreach ($texts as $text)
            <div class="marquee-icon">
              <span class="text">{{ $text }}</span>
              <div class="icon">
                <img src="{{ asset('assets/img/product/fire-shape.png') }}" alt="img" />
              </div>
            </div>
          @endforeach
        @endfor
      </div>
    @endfor
  </div>
</div>
