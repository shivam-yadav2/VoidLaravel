@if ($faqs->isNotEmpty())
<section class="faq-section section-padding fix">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="faq-content">
          <div class="section-title mb-0">
            <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">Everything You <br /> Need to Know</h2>
          </div>

          <div class="support-box-item">
            <div class="support-card style-2">
              <div class="icon">
                <img loading="lazy" decoding="async" src="{{ asset('assets/img/faq/icon-01.png') }}" alt="" />
              </div>
              <div class="content">
                <h3><a href="tel:{{ $site->contact_phone_link }}">{{ $site->contact_phone }}</a></h3>
                <p>Call us</p>
              </div>
            </div>
            <div class="support-card">
              <div class="icon">
                <img loading="lazy" decoding="async" src="{{ asset('assets/img/faq/icon-02.png') }}" alt="" />
              </div>
              <div class="content">
                <h3>{{ $site->contact_hours }}</h3>
                <p>Support hours</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="faq-items">
          <div class="accordion" id="faqAccordion">
            @foreach ($faqs as $i => $faq)
              @php
                $collapseId = 'faqCollapse' . $faq->id;
                $headingId = 'faqHeading' . $faq->id;
                $open = $i === 0;
              @endphp
              <div class="accordion-item wow fadeInUp" data-wow-delay="{{ min(0.3 + $i * 0.2, 1) }}s">
                <h3 class="accordion-header" id="{{ $headingId }}">
                  <button
                    class="accordion-button{{ $open ? '' : ' collapsed' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="{{ $open ? 'true' : 'false' }}"
                    aria-controls="{{ $collapseId }}"
                  >
                    <div class="dot"></div>
                    {{ $faq->question }}
                  </button>
                </h3>
                <div
                  id="{{ $collapseId }}"
                  class="accordion-collapse collapse{{ $open ? ' show' : '' }}"
                  aria-labelledby="{{ $headingId }}"
                  data-bs-parent="#faqAccordion"
                  role="region"
                >
                  <div class="accordion-body">
                    <p>{{ $faq->answer }}</p>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endif
