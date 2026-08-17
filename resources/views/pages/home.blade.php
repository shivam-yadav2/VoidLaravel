@extends('layouts.app')

@section('title', 'Home')

@section('content')
  @include('sections.landing.hero')
  @include('sections.landing.features-bar')
  @include('sections.landing.choose-us')
  @include('sections.landing.different-element')
  @include('sections.landing.showcase-product')
  @include('sections.landing.marquee-section-2')
  @include('sections.landing.responsive')
  @include('sections.landing.testimonial')
  @include('sections.landing.feature-highlights')
  @include('sections.landing.faq')
  @include('sections.landing.premium-feature')
@endsection
