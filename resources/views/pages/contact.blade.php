@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
  <div class="contact-page">
    @include('sections.contact.contact-hero')
    <section class="contact-main">
      <div class="container">
        <div class="contact-grid">
          @include('sections.contact.contact-info')
          @include('sections.contact.contact-form')
        </div>
      </div>
    </section>
  </div>
@endsection
