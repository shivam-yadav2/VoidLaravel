@extends('layouts.app')

@section('content')
  <section class="hero-section hero-1 fix bg-cover" style="background-image: url({{ asset('assets/img/hero/hero-bg.webp') }});">
    <div class="container">
      <div class="arrow-shape float-bob-x">
        <img src="{{ asset('assets/img/hero/arrow-icon.png') }}" alt="" />
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="hero-content">
            <h1 class="tx-title sec_title">Products we <br /> build &amp; run</h1>
            <p class="hero-text wow fadeInUp" data-wow-delay=".3s">
              Our own SaaS products and tools — designed, engineered and operated by the Void Layer team.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="products-section section-padding fix">
    <div class="container">
      @if ($products->isEmpty())
        <div class="blog-empty products-empty">
          <h2 class="h3">New products are in the works</h2>
          <p>We&rsquo;re putting the finishing touches on our first products. Want early access or a demo? Get in touch.</p>
          <a href="{{ route('contact') }}" class="theme-btn">
            <img src="{{ asset('assets/img/button-arrow.svg') }}" alt="" />
            Talk to us
          </a>
        </div>
      @else
        @include('pages.products.showcase', ['products' => $products])
      @endif
    </div>
  </section>
@endsection
