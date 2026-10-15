{{-- Product detail: used inside the modal and on /products/{slug}.
     $headingTag lets the standalone page keep a single <h1>. --}}
@php
  $video = $product->video();
  $slides = [];
  if ($video) {
      $slides[] = ['kind' => 'video', 'video' => $video, 'thumb' => $product->coverUrl() ?? $product->images->first()?->url(), 'alt' => $product->name.' — video'];
  }
  if ($product->coverUrl()) {
      $slides[] = ['kind' => 'image', 'src' => $product->coverUrl(), 'thumb' => $product->coverUrl(), 'alt' => $product->cover_image_alt ?: $product->name];
  }
  foreach ($product->images as $image) {
      $slides[] = ['kind' => 'image', 'src' => $image->url(), 'thumb' => $image->url(), 'alt' => $image->alt ?: $product->name.' screenshot'];
  }
  $headingTag = $headingTag ?? 'h2';
  $headingId = 'product-title-'.$product->id.($idSuffix ?? '');
@endphp

<div class="product-detail">
  <div class="product-detail__media" data-gallery>
    @if ($slides)
      <div class="pd-stage" data-gallery-stage>
        @foreach ($slides as $i => $slide)
          <figure class="pd-slide {{ $i === 0 ? 'is-active' : '' }}" data-slide @if ($i !== 0) aria-hidden="true" @endif>
            @if ($slide['kind'] === 'video' && $slide['video']['type'] === 'file')
              <video controls playsinline preload="metadata" @if ($slide['thumb']) poster="{{ $slide['thumb'] }}" @endif>
                <source src="{{ $slide['video']['src'] }}" />
              </video>
            @elseif ($slide['kind'] === 'video')
              {{-- The iframe is only created when this slide is shown, so a
                   closed modal never loads YouTube/Vimeo. --}}
              <div class="pd-embed" data-embed-src="{{ $slide['video']['src'] }}" data-embed-title="{{ $product->name }} video">
                @if ($slide['thumb'])
                  <img src="{{ $slide['thumb'] }}" alt="" loading="lazy" decoding="async" />
                @endif
                <button type="button" class="pd-embed__play" data-embed-play aria-label="Play {{ $product->name }} video">
                  <i class="fa-solid fa-play" aria-hidden="true"></i>
                </button>
              </div>
            @else
              <img src="{{ $slide['src'] }}" alt="{{ $slide['alt'] }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}" decoding="async" />
            @endif
          </figure>
        @endforeach

        @if (count($slides) > 1)
          <button type="button" class="pd-nav pd-nav--prev" data-gallery-prev aria-label="Previous"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>
          <button type="button" class="pd-nav pd-nav--next" data-gallery-next aria-label="Next"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
          <span class="pd-count" data-gallery-count aria-live="polite">1 / {{ count($slides) }}</span>
        @endif
      </div>

      @if (count($slides) > 1)
        <div class="pd-thumbs" role="tablist" aria-label="{{ $product->name }} media">
          @foreach ($slides as $i => $slide)
            <button type="button" class="pd-thumb {{ $i === 0 ? 'is-active' : '' }}" data-thumb="{{ $i }}" role="tab"
                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}" aria-label="{{ $slide['kind'] === 'video' ? 'Video' : 'Image '.$i }}">
              @if ($slide['thumb'])
                <img src="{{ $slide['thumb'] }}" alt="" loading="lazy" decoding="async" />
              @endif
              @if ($slide['kind'] === 'video')
                <span class="pd-thumb__play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
              @endif
            </button>
          @endforeach
        </div>
      @endif
    @else
      <div class="pd-stage pd-stage--empty">
        <img src="{{ $product->logoUrl() ?: asset('assets/img/void_logo.png') }}" alt="" />
      </div>
    @endif
  </div>

  <div class="product-detail__info" data-product-scroll>
    <header class="pd-header">
      <div class="pd-head">
        @if ($product->logoUrl())
          <img class="pd-logo" src="{{ $product->logoUrl() }}" alt="" width="56" height="56" />
        @endif
        <div class="pd-tags">
          @if ($product->category)
            <span class="pd-category">{{ $product->category }}</span>
          @endif
          <span class="pd-status pd-status--{{ $product->status }}">{{ $product->statusLabel() }}</span>
        </div>
      </div>

      <{{ $headingTag }} class="pd-name" id="{{ $headingId }}">{{ $product->name }}</{{ $headingTag }}>
      @if ($product->tagline)
        <p class="pd-tagline">{{ $product->tagline }}</p>
      @endif

      <div class="pd-actions">
        @if ($product->product_url)
          <a href="{{ $product->product_url }}" class="theme-btn" target="_blank" rel="noopener noreferrer">
            <img src="{{ asset('assets/img/button-arrow.svg') }}" alt="" />
            {{ $product->ctaLabel() }}
          </a>
        @endif
        @if ($inModal ?? false)
          <a href="{{ route('products.show', $product) }}" class="pd-pagelink">Open full page <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        @endif
      </div>
    </header>

    @if ($product->featureList())
      <section class="pd-block">
        <h3 class="pd-subhead">Key features</h3>
        <ul class="pd-features">
          @foreach ($product->featureList() as $feature)
            <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>{{ $feature }}</span></li>
          @endforeach
        </ul>
      </section>
    @endif

    <section class="pd-block">
      <h3 class="pd-subhead">Overview</h3>
      <div class="prose pd-description">
        @if ($product->description)
          {{-- Authored by an authenticated admin, stored and rendered as HTML. --}}
          {!! $product->description !!}
        @else
          <p>{{ $product->summary }}</p>
        @endif
      </div>
    </section>

    @if ($product->techList())
      <section class="pd-block">
        <h3 class="pd-subhead">Built with</h3>
        <ul class="tech-chips">
          @foreach ($product->techList() as $tech)
            <li>{{ $tech }}</li>
          @endforeach
        </ul>
      </section>
    @endif
  </div>
</div>
