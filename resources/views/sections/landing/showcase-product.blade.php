@php
  // What a client actually gets from working with Void Layer. (This section
  // previously still carried the purchased theme's marketplace copy —
  // "Figma files included", "One click install", "Buy once, use forever".)
  // Each icon is the theme's artwork chosen to match the benefit next to it.
  $benefits = [
    ['icon' => 'icon-01.svg', 'title' => 'Designs You Approve First', 'text' => 'Clickable designs of every key screen, signed off by you before a line of code is written.', 'delay' => '.2s'],
    ['icon' => 'icon-02.webp', 'title' => 'Ship, Then Iterate', 'text' => 'Working software in your hands within weeks, then improved every sprint from real feedback.', 'delay' => '.4s'],
    ['icon' => 'icon-06.webp', 'title' => 'You Own Everything', 'text' => 'Full source code, documentation and accounts are handed over to you. No lock-in, no licence fees to us.', 'delay' => '.5s'],
    ['icon' => 'icon-04.webp', 'title' => 'Hands-On Team Training', 'text' => 'We train your staff on the new system and stay close during rollout, so adoption actually sticks.', 'delay' => '.7s'],
    ['icon' => 'icon-03.webp', 'title' => 'Fast & Secure by Default', 'text' => 'Performance budgets, access control, backups and security reviews are part of every build, not extras.', 'delay' => '.8s', 'style' => 'style-2'],
    ['icon' => 'icon-05.webp', 'title' => 'Support After Launch', 'text' => 'Bug fixes, monitoring and new features after go-live, on a support plan that fits your business.', 'delay' => '.9s'],
  ];
@endphp
<section class="showcase-product-section section-padding fix section-bg">
  <div class="container">
    <div class="section-title title-two text-center">
      <span class="sub-title sub-two">
        <img loading="lazy" decoding="async" src="{{ asset('assets/img/title/icon-02.svg') }}" alt="" />
        Client Benefits
      </span>
      <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">What You Get Working <br /> With Us</h2>
    </div>
    <div class="row">
      @foreach (array_chunk($benefits, 2) as $column)
        <div class="col-xl-4 col-lg-6 col-md-6">
          @foreach ($column as $benefit)
            <div class="showcase-product-box {{ $benefit['style'] ?? '' }} wow fadeInUp" data-wow-delay="{{ $benefit['delay'] }}">
              <div class="icon">
                <img loading="lazy" decoding="async" src="{{ asset('assets/img/product/'.$benefit['icon']) }}" alt="" width="70" height="70" />
              </div>
              <div class="content">
                <h3>{{ $benefit['title'] }}</h3>
                <p>{{ $benefit['text'] }}</p>
              </div>
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  </div>
</section>
