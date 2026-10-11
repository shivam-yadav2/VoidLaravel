{{-- A product card. It's a real link to /products/{slug} (works without JS
     and for crawlers); the showcase script opens the detail modal instead. --}}
<a href="{{ route('products.show', $product) }}" class="product-card {{ $product->is_featured ? 'product-card--featured' : '' }}"
   data-product-open="{{ $product->slug }}" aria-haspopup="dialog">
  <span class="product-card__media">
    @if ($product->coverUrl())
      <img src="{{ $product->coverUrl() }}" alt="" loading="lazy" decoding="async" />
    @elseif ($product->images->first())
      <img src="{{ $product->images->first()->url() }}" alt="" loading="lazy" decoding="async" />
    @else
      <span class="product-card__placeholder"><img src="{{ $product->logoUrl() ?: asset('assets/img/void_logo.png') }}" alt="" /></span>
    @endif
    @if ($product->video())
      <span class="product-card__video" aria-hidden="true"><i class="fa-solid fa-play"></i> Video</span>
    @endif
  </span>

  <span class="product-card__body">
    <span class="product-card__top">
      @if ($product->logoUrl())
        <img class="product-card__logo" src="{{ $product->logoUrl() }}" alt="" width="44" height="44" loading="lazy" decoding="async" />
      @endif
      <span class="product-card__tags">
        @if ($product->category)<span class="pd-category">{{ $product->category }}</span>@endif
        <span class="pd-status pd-status--{{ $product->status }}">{{ $product->statusLabel() }}</span>
      </span>
    </span>
    <span class="product-card__name">{{ $product->name }}</span>
    <span class="product-card__summary">{{ $product->tagline ?: $product->summary }}</span>
    @if ($product->is_featured && $product->tagline)
      {{-- Featured cards have room for the fuller pitch as well. --}}
      <span class="product-card__pitch">{{ $product->summary }}</span>
    @endif
    <span class="product-card__more">View details <span class="product-card__plus" aria-hidden="true"></span></span>
  </span>
</a>
