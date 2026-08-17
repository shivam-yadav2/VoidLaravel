@php
  $devices = [
    ['icon' => 'device-01.png', 'title' => '1920 x 1080px', 'subtitle' => 'Full Screen Layout', 'delay' => '.3s'],
    ['icon' => 'device-02.png', 'title' => '1364 x 768px', 'subtitle' => 'Notebook Screen Layout', 'delay' => '.5s'],
    ['icon' => 'device-03.png', 'title' => '1024 x 764px', 'subtitle' => 'Tablet Screen Layout', 'delay' => '.7s'],
    ['icon' => 'device-04.png', 'title' => '767 x 365px', 'subtitle' => 'Mobile Screen Layout', 'delay' => '.9s'],
  ];
@endphp
<div class="responsive-section section-padding fix pb-0">
  <div class="bootom-shape">
    <img src="{{ asset('assets/img/responsive/shadow-shape.png') }}" alt="img" />
  </div>
  <div class="hand-shape float-bob-y">
    <img src="{{ asset('assets/img/responsive/hand.png') }}" alt="img" />
  </div>
  <div class="arrow-shape float-bob-x">
    <img src="{{ asset('assets/img/responsive/arrow.png') }}" alt="img" />
  </div>
  <div class="text-shape float-bob-y">
    <img src="{{ asset('assets/img/responsive/text-arrow.png') }}" alt="img" />
  </div>
  <div class="star-shape float-bob-x">
    <img src="{{ asset('assets/img/responsive/star.png') }}" alt="img" />
  </div>
  <div class="emoji-shape float-bob-y">
    <img src="{{ asset('assets/img/responsive/emoji.png') }}" alt="img" />
  </div>
  <div class="container">
    <div class="responsive-wrapper">
      <div class="section-title text-center">
        <span class="sub-title wow fadeInUp">
          <img src="{{ asset('assets/img/title/icon-08.svg') }}" alt="img" />
          responsive device
        </span>
        <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">Fully Responsive Layouts <br /> For Any Device</h2>
      </div>
      <div class="responsive-thumb wow fadeInUp" data-wow-delay=".2s">
        <img src="{{ asset('assets/img/responsive/image-001.png') }}" alt="img" />
      </div>
      <div class="bottom-device-area">
        @foreach ($devices as $device)
          <div class="icon-items wow fadeInUp" data-wow-delay="{{ $device['delay'] }}">
            <div class="icon">
              <img src="{{ asset('assets/img/responsive/' . $device['icon']) }}" alt="img" />
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
