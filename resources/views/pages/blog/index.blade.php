@extends('layouts.app')

@section('content')
  <section class="hero-section hero-1 fix bg-cover blog-hero" style="background-image: url({{ asset('assets/img/hero/hero-bg.webp') }});">
    <div class="container">
      <div class="arrow-shape float-bob-x">
        <img src="{{ asset('assets/img/hero/arrow-icon.png') }}" alt="" />
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="hero-content">
            <h1 class="tx-title sec_title">
              {{ $activeCategory?->name ?? 'Notes from' }} <br /> {{ $activeCategory ? 'on the blog' : 'the build' }}
            </h1>
            <p class="hero-text wow fadeInUp" data-wow-delay=".3s">
              {{ $activeCategory?->description
                  ?: 'Practical writing on custom software, ERP & HRMS, automation and what it actually takes to ship and operate a product.' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="blog-section section-padding fix">
    <div class="container">
      <div class="blog-toolbar">
        <div class="blog-filters">
          <a href="{{ route('blog.index') }}" class="blog-chip {{ ! $activeCategory ? 'is-active' : '' }}">All posts</a>
          @foreach ($categories as $category)
            @continue($category->posts_count === 0)
            <a href="{{ route('blog.category', $category) }}"
               class="blog-chip {{ $activeCategory?->id === $category->id ? 'is-active' : '' }}">
              {{ $category->name }} <span>{{ $category->posts_count }}</span>
            </a>
          @endforeach
        </div>

        <form method="GET" action="{{ route('blog.index') }}" class="blog-search" role="search">
          <input type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="Search posts…" aria-label="Search posts" />
          <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
        </form>
      </div>

      @if ($posts->isEmpty())
        <div class="blog-empty">
          <h3>Nothing here yet</h3>
          <p>{{ $q ? 'No posts match that search — try a different term.' : 'The first post is on its way. Check back soon.' }}</p>
          @if ($q)
            <a href="{{ route('blog.index') }}" class="theme-btn">
              <img src="{{ asset('assets/img/button-arrow.svg') }}" alt="" />
              View all posts
            </a>
          @endif
        </div>
      @else
        <div class="blog-grid">
          @foreach ($posts as $post)
            <article class="blog-card wow fadeInUp" data-wow-delay=".{{ ($loop->index % 3) + 2 }}s">
              <a href="{{ route('blog.show', $post) }}" class="blog-card__media">
                @if ($post->imageUrl())
                  <img src="{{ $post->imageUrl() }}" alt="{{ $post->featured_image_alt ?: $post->title }}" loading="lazy" />
                @else
                  <div class="blog-card__placeholder" aria-hidden="true">
                    <img src="{{ asset('assets/img/void_logo.png') }}" alt="" />
                  </div>
                @endif
              </a>

              <div class="blog-card__body">
                <div class="blog-card__meta">
                  @if ($post->category)
                    <a href="{{ route('blog.category', $post->category) }}" class="blog-card__cat">{{ $post->category->name }}</a>
                  @endif
                  <time datetime="{{ $post->published_at->displayTz()->toDateString() }}">{{ $post->published_at->displayTz()->format('d M Y') }}</time>
                  <span>{{ $post->readingMinutes() }} min read</span>
                </div>

                <h2 class="blog-card__title">
                  <a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
                </h2>

                @if ($post->excerpt)
                  <p class="blog-card__excerpt">{{ $post->excerpt }}</p>
                @endif

                <a href="{{ route('blog.show', $post) }}" class="blog-card__link">
                  Read article<span class="visually-hidden-label">: {{ $post->title }}</span> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
              </div>
            </article>
          @endforeach
        </div>

        @if ($posts->hasPages())
          <div class="blog-pagination">{{ $posts->links() }}</div>
        @endif
      @endif
    </div>
  </section>
@endsection
