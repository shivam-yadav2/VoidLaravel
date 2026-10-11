@extends('layouts.app')

@section('content')
  <article class="blog-post">
    <header class="blog-post__hero bg-cover" style="background-image: url({{ asset('assets/img/hero/hero-bg.webp') }});">
      <div class="container">
        <nav class="blog-crumbs" aria-label="Breadcrumb">
          <a href="{{ route('home') }}">Home</a>
          <span>/</span>
          <a href="{{ route('blog.index') }}">Blog</a>
          @if ($post->category)
            <span>/</span>
            <a href="{{ route('blog.category', $post->category) }}">{{ $post->category->name }}</a>
          @endif
        </nav>

        <h1 class="blog-post__title tx-title">{{ $post->title }}</h1>

        <div class="blog-post__meta">
          <time datetime="{{ $post->published_at->displayTz()->toDateString() }}">{{ $post->published_at->displayTz()->format('d F Y') }}</time>
          <span class="blog-post__dot"></span>
          <span>{{ $post->readingMinutes() }} min read</span>
          @if ($post->author)
            <span class="blog-post__dot"></span>
            <span>By {{ $post->author->name }}</span>
          @endif
        </div>
      </div>
    </header>

    <div class="container">
      @if ($post->imageUrl())
        <figure class="blog-post__figure">
          <img src="{{ $post->imageUrl() }}" alt="{{ $post->featured_image_alt ?: $post->title }}" />
        </figure>
      @endif

      <div class="blog-post__layout">
        <div class="blog-post__content prose">
          {{-- Authored by an authenticated admin, stored and rendered as HTML. --}}
          {!! $post->content !!}
        </div>

        <aside class="blog-post__side">
          <div class="blog-side-card">
            <p class="blog-side-card__kicker">Share</p>
            <div class="blog-share">
              <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('blog.show', $post)) }}"
                 target="_blank" rel="noopener" aria-label="Share on LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
              <a href="https://x.com/intent/tweet?url={{ urlencode(route('blog.show', $post)) }}&text={{ urlencode($post->title) }}"
                 target="_blank" rel="noopener" aria-label="Share on X"><i class="fa-brands fa-x-twitter"></i></a>
              <a href="https://wa.me/?text={{ urlencode($post->title.' '.route('blog.show', $post)) }}"
                 target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
          </div>

          <div class="blog-side-card blog-side-cta">
            <p class="blog-side-card__kicker">Got a project?</p>
            <h3>Let&rsquo;s build it properly.</h3>
            <p>Tell us what you&rsquo;re working on — we reply within one business day.</p>
            <a href="{{ route('contact') }}" class="theme-btn">
              <img src="{{ asset('assets/img/button-arrow.svg') }}" alt="" />
              Start a project
            </a>
          </div>
        </aside>
      </div>

      @if ($related->isNotEmpty())
        <section class="blog-related">
          <div class="section-title">
            <h2 class="tx-title sec_title">Keep reading</h2>
          </div>
          <div class="blog-grid">
            @foreach ($related as $item)
              <article class="blog-card">
                <a href="{{ route('blog.show', $item) }}" class="blog-card__media">
                  @if ($item->imageUrl())
                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->featured_image_alt ?: $item->title }}" loading="lazy" />
                  @else
                    <div class="blog-card__placeholder" aria-hidden="true">
                      <img src="{{ asset('assets/img/void_logo.png') }}" alt="" />
                    </div>
                  @endif
                </a>
                <div class="blog-card__body">
                  <div class="blog-card__meta">
                    <time datetime="{{ $item->published_at->displayTz()->toDateString() }}">{{ $item->published_at->displayTz()->format('d M Y') }}</time>
                    <span>{{ $item->readingMinutes() }} min read</span>
                  </div>
                  <h3 class="blog-card__title">
                    <a href="{{ route('blog.show', $item) }}">{{ $item->title }}</a>
                  </h3>
                </div>
              </article>
            @endforeach
          </div>
        </section>
      @endif
    </div>
  </article>
@endsection
