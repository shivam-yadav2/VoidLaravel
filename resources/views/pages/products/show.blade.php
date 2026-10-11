@extends('layouts.app')

@section('content')
  <section class="hero-section hero-1 fix bg-cover product-page-hero" style="background-image: url({{ asset('assets/img/hero/hero-bg.webp') }});">
    <div class="container">
      <div class="hero-content">
        <nav class="page-crumbs" aria-label="Breadcrumb">
          <a href="{{ route('home') }}">Home</a>
          <span aria-hidden="true">/</span>
          <a href="{{ route('products.index') }}">Products</a>
          <span aria-hidden="true">/</span>
          <span aria-current="page">{{ $product->name }}</span>
        </nav>
      </div>
    </div>
  </section>

  <section class="products-section product-page fix">
    <div class="container">
      <div class="product-page__card">
        @include('pages.products.detail', ['product' => $product, 'headingTag' => 'h1'])
      </div>

      @if ($more->isNotEmpty())
        <div class="product-page__more">
          <h2 class="tx-title sec_title">More products</h2>
          @include('pages.products.showcase', ['products' => $more])
        </div>
      @endif
    </div>
  </section>
@endsection
