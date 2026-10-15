{{-- A product card. It's a real link to /products/{slug} (works without JS
     and for crawlers); the showcase script opens the detail modal instead. --}}
@php
  $cover = $product->coverUrl() ?? $product->images->first()?->url();
  $host = $product->product_url ? parse_url($product->product_url, PHP_URL_HOST) : null;
  // Feature lines read "Title — detail"; the card only has room for the title.
  $highlights = array_slice(array_map(fn ($f) => trim(preg_split('/\s+[—–-]\s+/u', $f)[0]), $product->featureList()), 0, 4);
  $screens = $product->images->count() + ($product->coverUrl() ? 1 : 0);
@endphp
<a href="{{ route('products.show', $product) }}" class="product-card {{ $product->is_featured ? 'product-card--featured' : '' }}"
   data-product-open="{{ $product->slug }}" aria-haspopup="dialog">
  <span class="product-card__body">
    <span class="product-card__top">
      @if ($product->logoUrl())
        <img class="product-card__logo" src="{{ $product->logoUrl() }}" alt="" width="48" height="48" loading="lazy" decoding="async" />
      @endif
      <span class="product-card__tags">
        @if ($product->category)<span class="pd-category">{{ $product->category }}</span>@endif
        <span class="pd-status pd-status--{{ $product->status }}">{{ $product->statusLabel() }}</span>
      </span>
    </span>

    <span class="product-card__name">{{ $product->name }}</span>
    @if ($product->tagline)
      <span class="product-card__tagline">{{ $product->tagline }}</span>
    @endif
    <span class="product-card__summary">{{ $product->summary }}</span>

    @if ($highlights)
      <span class="product-card__chips">
        @foreach ($highlights as $highlight)
          <span class="product-card__chip">{{ $highlight }}</span>
        @endforeach
      </span>
    @endif

    <span class="product-card__foot">
      <span class="product-card__btn">
        Explore product
        <span class="product-card__btn-icon" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
      </span>
      @if ($screens > 1 || $product->video())
        <span class="product-card__meta">
          @if ($product->video())<span><i class="fa-solid fa-play" aria-hidden="true"></i> Video</span>@endif
          @if ($screens > 1)<span><i class="fa-regular fa-images" aria-hidden="true"></i> {{ $screens }} screens</span>@endif
        </span>
      @endif
    </span>
  </span>

  <span class="product-card__media" aria-hidden="true">
    <span class="product-card__frame">
      <span class="product-card__chrome">
        <i></i><i></i><i></i>
        @if ($host)<span class="product-card__url">{{ $host }}</span>@endif
      </span>
      @if ($cover)
        <img src="{{ $cover }}" alt="" loading="lazy" decoding="async" />
      @else
        <span class="product-card__placeholder"><img src="{{ $product->logoUrl() ?: asset('assets/img/void_logo.png') }}" alt="" /></span>
      @endif
    </span>
  </span>
</a>
