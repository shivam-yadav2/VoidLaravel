@extends('layouts.app')

@section('title', 'Services')

@section('content')
  @include('sections.services.services-hero')
  @include('sections.services.services-list')
  @include('sections.services.services-approach')
  @include('sections.services.services-video')
  @include('sections.services.services-process')
@endsection
