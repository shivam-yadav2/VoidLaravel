@extends('layouts.app')

@section('content')
  <section class="policy-page">
    <header class="policy-hero bg-cover" style="background-image: url({{ asset('assets/img/hero/hero-bg.jpg') }});">
      <div class="container">
        <nav class="blog-crumbs" aria-label="Breadcrumb">
          <a href="{{ route('home') }}">Home</a>
          <span>/</span>
          <span>{{ $page->title }}</span>
        </nav>
        <h1 class="tx-title sec_title">{{ $page->title }}</h1>
        <p class="policy-hero__updated">Last updated {{ $page->updated_at->format('d F Y') }}</p>
      </div>
    </header>

    <div class="container">
      <div class="policy-body prose">
        {{-- Authored by an authenticated admin, stored and rendered as HTML. --}}
        {!! $page->content !!}
      </div>
    </div>
  </section>
@endsection
