@php
  $trailImages = ['hover-img-01.jpg', 'hover-img-02.jpg', 'hover-img-03.jpg', 'hover-img-04.jpg', 'hover-img-05.jpg', 'hover-img-06.jpg', 'hover-img-07.jpg', 'hover-img-08.jpg', 'hover-img-09.jpg'];

  $rowOne = [
    ['icon' => 'icon-01.png', 'label' => 'Custom Software'],
    ['icon' => 'icon-02.png', 'label' => 'ERP Systems'],
    ['icon' => 'icon-03.png', 'label' => 'HRMS Solutions'],
    ['icon' => 'icon-04.png', 'label' => 'Web Applications'],
    ['icon' => 'icon-05.png', 'label' => 'Business Automation'],
    ['icon' => 'icon-06.png', 'label' => 'AI Solutions'],
    ['icon' => 'icon-07.png', 'label' => 'SaaS Products'],
    ['icon' => 'icon-08.png', 'label' => 'Cloud Hosting'],
    ['icon' => 'icon-09.png', 'label' => 'API Integration'],
    ['icon' => 'icon-10.png', 'label' => 'Data Security'],
    ['icon' => 'icon-11.png', 'label' => 'Quality Assurance'],
  ];

  $rowTwo = [
    ['icon' => 'icon-12.png', 'label' => 'Agile Delivery'],
    ['icon' => 'icon-13.png', 'label' => 'Dedicated Support'],
    ['icon' => 'icon-14.png', 'label' => 'Scalable Architecture'],
    ['icon' => 'icon-15.png', 'label' => 'DevOps'],
    ['icon' => 'icon-16.png', 'label' => 'UI/UX Design'],
    ['icon' => 'icon-17.png', 'label' => 'Database Design'],
    ['icon' => 'icon-18.png', 'label' => 'Third-Party Integration'],
    ['icon' => 'icon-19.png', 'label' => 'Legacy Modernization'],
    ['icon' => 'icon-20.png', 'label' => 'Maintenance'],
    ['icon' => 'icon-21.png', 'label' => 'Consulting'],
  ];
@endphp
<section class="premium-feature-section section-padding section-bg image-trail-animation fix">
  <div class="image-trail-images">
    @foreach ($trailImages as $img)
      <img class="content__img" src="{{ asset('assets/img/feature/' . $img) }}" alt="Trail Image" />
    @endforeach
  </div>
  <div class="container">
    <div class="hand-shape float-bob-y">
      <img src="{{ asset('assets/img/feature/hand.png') }}" alt="img" />
    </div>
    <div class="emoji-shape float-bob-x">
      <img src="{{ asset('assets/img/feature/emoji-01.png') }}" alt="img" />
    </div>
    <div class="section-title-area">
      <div class="section-title title-two">
        <div class="counter-title-item">
          <div class="title-counter">
            <h2><span class="count">50</span>+</h2>
          </div>
          <span class="sub-title sub-two mb-0 wow fadeInUp">
            <img src="{{ asset('assets/img/title/icon-04.svg') }}" alt="img" />
            Built to scale with you
          </span>
        </div>
      </div>
      <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">Solutions <br /> Built <br /> Around You</h2>
    </div>
  </div>
  <div class="marquee-feature mb-4">
    @for ($i = 0; $i < 3; $i++)
      <div class="feature-marquee-group">
        @foreach ($rowOne as $item)
          <div class="feature-item">
            <span>
              <img src="{{ asset('assets/img/feature/icon/' . $item['icon']) }}" alt="img" />
              {{ $item['label'] }}
            </span>
          </div>
        @endforeach
      </div>
    @endfor
  </div>

  <div class="marquee-feature mb-4">
    @for ($i = 0; $i < 3; $i++)
      <div class="feature-marquee-group left-to-right">
        @foreach ($rowTwo as $item)
          <div class="feature-item">
            <span>
              <img src="{{ asset('assets/img/feature/icon/' . $item['icon']) }}" alt="img" />
              {{ $item['label'] }}
            </span>
          </div>
        @endforeach
      </div>
    @endfor
  </div>

  <div class="feature-big-text wow fadeInUp" data-wow-delay=".5s">
    <h2 class="name">VOIDLAYER</h2>
  </div>
</section>
