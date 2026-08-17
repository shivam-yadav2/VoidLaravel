@php
  $cards = \App\Models\FeatureCard::group('services')->active()->get();
@endphp
@if ($cards->isNotEmpty())
<section id="services" class="core-features-section section-padding section-bg fix">
  <div class="container">
    <div class="section-title-area">
      <div class="section-title title-two">
        <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">What We <br /> Build</h2>
      </div>
      <p class="wow fadeInUp" data-wow-delay=".3s">
        One team across the whole stack — product, design and engineering <br />
        working together so your platform, apps and infrastructure ship <br />
        as a single, coherent system.
      </p>
    </div>
    <div class="row">
      @foreach ($cards as $i => $card)
        <div class="col-xl-4 col-lg-6 wow fadeInUp" data-wow-delay="{{ min(0.3 + $i * 0.2, 1) }}s">
          <div class="feature-card-item">
            <div class="core-feature-image">
              <img src="{{ $card->iconUrl() ?: asset('assets/img/core-feature/image-0'.((($i % 5)) + 1).'.png') }}" alt="img" />
            </div>
            <div class="feature-content">
              <h3>{{ $card->title }}</h3>
              @if ($card->description)
                <p>{{ $card->description }}</p>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif
