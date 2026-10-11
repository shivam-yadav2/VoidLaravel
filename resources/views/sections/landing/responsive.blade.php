@php
  $devices = [
    ['icon' => 'device-01.png', 'title' => 'Desktop', 'subtitle' => 'Full dashboards & admin panels', 'delay' => '.3s'],
    ['icon' => 'device-02.png', 'title' => 'Laptop', 'subtitle' => 'Everyday office workflows', 'delay' => '.5s'],
    ['icon' => 'device-03.png', 'title' => 'Tablet', 'subtitle' => 'Field teams & shop floors', 'delay' => '.7s'],
    ['icon' => 'device-04.png', 'title' => 'Mobile', 'subtitle' => 'Approvals & updates on the go', 'delay' => '.9s'],
  ];
@endphp
<div class="responsive-section section-padding fix pb-0">
  <div class="bootom-shape">
    <img loading="lazy" decoding="async" src="{{ asset('assets/img/responsive/shadow-shape.png') }}" alt="" />
  </div>
  <div class="hand-shape float-bob-y">
    <img loading="lazy" decoding="async" src="{{ asset('assets/img/responsive/hand.png') }}" alt="" />
  </div>
  <div class="arrow-shape float-bob-x">
    <img loading="lazy" decoding="async" src="{{ asset('assets/img/responsive/arrow.png') }}" alt="" />
  </div>
  <div class="text-shape float-bob-y">
    <img loading="lazy" decoding="async" src="{{ asset('assets/img/responsive/text-arrow.png') }}" alt="" />
  </div>
  <div class="star-shape float-bob-x">
    <img loading="lazy" decoding="async" src="{{ asset('assets/img/responsive/star.png') }}" alt="" />
  </div>
  <div class="emoji-shape float-bob-y">
    <img loading="lazy" decoding="async" src="{{ asset('assets/img/responsive/emoji.png') }}" alt="" />
  </div>
  <div class="container">
    <div class="responsive-wrapper">
      <div class="section-title text-center">
        <span class="sub-title wow fadeInUp">
          <img loading="lazy" decoding="async" src="{{ asset('assets/img/title/icon-08.svg') }}" alt="" />
          Works everywhere
        </span>
        <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">Software Your Team Can Use <br /> On Any Device</h2>
      </div>
      <div class="responsive-thumb wow fadeInUp" data-wow-delay=".2s">
        <img loading="lazy" decoding="async" src="{{ asset('assets/img/responsive/image-001.webp') }}" alt="A Void Layer dashboard shown on desktop, tablet and mobile screens" />
      </div>
      <div class="bottom-device-area">
        @foreach ($devices as $device)
          <div class="icon-items wow fadeInUp" data-wow-delay="{{ $device['delay'] }}">
            <div class="icon">
              <img loading="lazy" decoding="async" src="{{ asset('assets/img/responsive/' . $device['icon']) }}" alt="" />
            </div>
            <div class="content">
              <h3>{{ $device['title'] }}</h3>
              <p>{{ $device['subtitle'] }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
