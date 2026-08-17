@php
  $testimonials = \App\Models\Testimonial::active()->get();
  $starsFor = fn (int $rating) => '<div class="rating">'
      . str_repeat('<i class="fa-solid fa-star"></i>', $rating)
      . str_repeat('<i class="fa-regular fa-star"></i>', 5 - $rating)
      . '</div>';
@endphp
@if ($testimonials->isNotEmpty())
<section class="testimonial-section section-padding fix section-bg">
  <div class="container">
    <div class="section-title text-center">
      <div class="title-counter">
        <span class="title-count"><span class="count">{{ $testimonials->count() }}</span>+</span>
      </div>
      <span class="sub-title">
        <img src="{{ asset('assets/img/title/icon-12.svg') }}" alt="img" />
        Trusted Customers
      </span>
      <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">Testimonials From Real <br /> Customers</h2>
    </div>
  </div>

  <div class="swiper template-slider">
    <div class="swiper-wrapper slide-transtion">
      @foreach ($testimonials as $t)
        <div class="swiper-slide brand-slide-element">
          <div class="testimonial-card">
            <div class="testimonial-top">
              <div class="author-info">
                @if ($t->photoUrl())
                  <img src="{{ $t->photoUrl() }}" alt="{{ $t->client_name }}" />
                @endif
                <div class="content">
                  <h3>{{ $t->client_name }}</h3>
                  <span>{{ trim(($t->client_role ?? '') . ($t->client_company ? ' · ' . $t->client_company : '')) }}</span>
                </div>
              </div>
              <div class="quote-icon">
                <img src="{{ asset('assets/img/testimonial/koma.svg') }}" alt="img" />
              </div>
            </div>

            {!! $starsFor($t->rating) !!}

            <p class="testimonial-text">&ldquo;{{ $t->quote }}&rdquo;</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="container">
    <div class="testimonial-btn-area">
      <a href="{{ route('contact') }}" class="theme-btn">
        <i class="fa-solid fa-arrow-right"></i>
        Start a project
      </a>
    </div>
  </div>
</section>
@endif
