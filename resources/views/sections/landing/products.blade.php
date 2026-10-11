{{-- Homepage "Our products" section — $products / $productTotal come from the
     view composer in AppServiceProvider. Hidden entirely until a product is
     published at /admin/products. --}}
@if ($products->isNotEmpty())
<section class="products-section products-home section-padding fix" id="products">
  <div class="container">
    <div class="products-home__head">
      <div class="section-title mb-0">
        <span class="sub-title wow fadeInUp">
          <img loading="lazy" decoding="async" src="{{ asset('assets/img/title/icon-02.svg') }}" alt="" />
          Our products
        </span>
        <h2 class="tx-title sec_title tz-itm-title tz-itm-anim">Products we <br /> build &amp; run</h2>
      </div>
      @if ($productTotal > $products->count())
        <a href="{{ route('products.index') }}" class="theme-btn products-home__all">
          <img src="{{ asset('assets/img/button-arrow.svg') }}" alt="" />
          All products
        </a>
      @endif
    </div>

    @include('pages.products.showcase', ['products' => $products])
  </div>
</section>
@endif
