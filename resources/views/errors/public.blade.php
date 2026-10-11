{{-- Shared body for branded error pages that can safely use the full site
     layout (404 / 419 / 429). Expects $code, $heading, $message. 500/503 do
     NOT use this: they must render even when the database is down. --}}
@php
  $seo = ['title' => $heading, 'description' => $message, 'noindex' => true];
  // Suggested pages = the main menu (config/site.php → nav), minus Home,
  // which already has its own button.
  $suggestions = array_values(array_filter(config('site.nav'), fn ($item) => $item[0] !== 'home'));
@endphp
@extends('layouts.app')

@section('content')
  <section class="error-hero bg-cover" style="background-image: url({{ asset('assets/img/hero/hero-bg.webp') }});">
    <span class="error-hero__digits" aria-hidden="true">{{ $code }}</span>

    <div class="container">
      <div class="error-hero__inner">
        <h1 class="error-hero__title wow fadeInUp" data-wow-delay=".1s">{{ $heading }}</h1>
        <p class="error-hero__text wow fadeInUp" data-wow-delay=".2s">{{ $message }}</p>

        <div class="error-hero__actions wow fadeInUp" data-wow-delay=".3s">
          <a href="{{ route('home') }}" class="theme-btn">
            <img src="{{ asset('assets/img/button-arrow.svg') }}" alt="" />
            Back to home
          </a>
          <a href="{{ route('contact') }}" class="error-hero__ghost">Contact us</a>
        </div>

        <nav class="error-hero__links wow fadeInUp" data-wow-delay=".4s" aria-label="Popular pages">
          <span class="error-hero__links-label">Or try</span>
          @foreach ($suggestions as [$route, $match, $label])
            <a href="{{ route($route) }}">{{ $label }}</a>
          @endforeach
        </nav>
      </div>
    </div>
  </section>
@endsection
