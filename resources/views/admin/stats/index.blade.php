@extends('admin.layout')

@section('title', 'Stats')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Achievement stats</h1>
      <p class="adm-muted">The counter row on the About page (e.g. "50+ Products shipped").</p>
    </div>
    <a href="{{ route('admin.stats.create') }}" class="adm-btn adm-btn--primary">Add stat</a>
  </div>

  @if ($stats->isEmpty())
    <div class="adm-empty"><p>No stats yet.</p></div>
  @else
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead><tr><th></th><th>Value</th><th>Label</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @foreach ($stats as $stat)
            <tr>
              <td>@if ($stat->iconUrl())<img src="{{ $stat->iconUrl() }}" alt="" class="adm-avatar" />@endif</td>
              <td><a href="{{ route('admin.stats.edit', $stat) }}" class="adm-link">{{ $stat->value }}</a></td>
              <td class="adm-small">{{ $stat->label }}</td>
              <td>{{ $stat->sort_order }}</td>
              <td><span class="adm-badge adm-badge--{{ $stat->is_active ? 'contacted' : 'archived' }}">{{ $stat->is_active ? 'active' : 'hidden' }}</span></td>
              <td class="adm-right"><a href="{{ route('admin.stats.edit', $stat) }}" class="adm-btn adm-btn--sm">Edit</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
@endsection
