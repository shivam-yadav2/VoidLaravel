{{-- $testimonials comes from the view composer in AppServiceProvider. The
     whole section stays hidden until at least one testimonial is active. --}}
@if ($testimonials->isNotEmpty())
<section class="testimonial-section section-padding fix section-bg">
  <div class="container">
    <div class="section-title text-center">
      <span class="sub-title">
        <img loading="lazy" decoding="async" src="{{ asset('assets/img/title/icon-12.svg') }}" alt="" />
        Client Feedback
      </span>
      <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">What Our Clients <br /> Say</h2>
    </div>
  </div>

  <div class="swiper template-slider">
    <div class="swiper-wrapper slide-transtion">
      @foreach ($testimonials as $t)
        <div class="swiper-slide brand-slide-element">
          <figure class="testimonial-card">
            <div class="testimonial-top">
              <div class="author-info">
                @if ($t->photoUrl())
                  <img loading="lazy" decoding="async" src="{{ $t->photoUrl() }}" alt="{{ $t->client_name }}" width="56" height="56" />
                @endif
                <figcaption class="content">
                  <h3>{{ $t->client_name }}</h3>
                  <span>{{ trim(($t->client_role ?? '') . ($t->client_role && $t->client_company ? ' · ' : '') . ($t->client_company ?? '')) }}</span>
                </figcaption>
              </div>
              <div class="quote-icon">
                <img loading="lazy" decoding="async" src="{{ asset('assets/img/testimonial/koma.svg') }}" alt="" />
              </div>
            </div>

            <div class="rating" role="img" aria-label="Rated {{ $t->rating }} out of 5">
              @for ($star = 1; $star <= 5; $star++)
                <i class="{{ $star <= $t->rating ? 'fa-solid' : 'fa-regular' }} fa-star" aria-hidden="true"></i>
              @endfor
            </div>

            <blockquote class="testimonial-text">&ldquo;{{ $t->quote }}&rdquo;</blockquote>
          </figure>
        </div>
      @endforeach
    </div>
  </div>

  <div class="container">
    <div class="testimonial-btn-area">
      <a href="{{ route('contact') }}" class="theme-btn">
        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        Start a project
      </a>
    </div>
  </div>
</section>
@endif
