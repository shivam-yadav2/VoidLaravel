@extends('admin.layout')

@section('title', 'Tech badges')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Tech badges</h1>
      <p class="adm-muted">The small icon badges in the "Core Features" strip on the homepage.</p>
    </div>
    <a href="{{ route('admin.tech-badges.create') }}" class="adm-btn adm-btn--primary">Add badge</a>
  </div>

  @if ($badges->isEmpty())
    <div class="adm-empty"><p>No badges yet.</p></div>
  @else
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead><tr><th></th><th>Title</th><th>Subtitle</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @foreach ($badges as $badge)
            <tr>
              <td>@if ($badge->iconUrl())<img src="{{ $badge->iconUrl() }}" alt="" class="adm-avatar" />@endif</td>
              <td><a href="{{ route('admin.tech-badges.edit', $badge) }}" class="adm-link">{{ $badge->title }}</a></td>
              <td class="adm-small">{{ $badge->subtitle }}</td>
              <td>{{ $badge->sort_order }}</td>
              <td><span class="adm-badge adm-badge--{{ $badge->is_active ? 'contacted' : 'archived' }}">{{ $badge->is_active ? 'active' : 'hidden' }}</span></td>
              <td class="adm-right"><a href="{{ route('admin.tech-badges.edit', $badge) }}" class="adm-btn adm-btn--sm">Edit</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
@endsection
