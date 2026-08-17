@extends('admin.layout')

@section('title', 'Testimonials')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Testimonials</h1>
      <p class="adm-muted">Shown in the testimonial carousel on the homepage.</p>
    </div>
    <a href="{{ route('admin.testimonials.create') }}" class="adm-btn adm-btn--primary">Add testimonial</a>
  </div>

  @if ($testimonials->isEmpty())
    <div class="adm-empty">
      <p>No testimonials yet.</p>
      <p class="adm-muted">Add real client quotes — the homepage shipped with placeholder names.</p>
    </div>
  @else
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead><tr><th></th><th>Client</th><th>Quote</th><th>Rating</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @foreach ($testimonials as $t)
            <tr>
              <td>
                @if ($t->photoUrl())
                  <img src="{{ $t->photoUrl() }}" alt="" class="adm-avatar" />
                @else
                  <span class="adm-avatar adm-avatar--placeholder">{{ strtoupper(substr($t->client_name, 0, 1)) }}</span>
                @endif
              </td>
              <td>
                <a href="{{ route('admin.testimonials.edit', $t) }}" class="adm-link">{{ $t->client_name }}</a>
                <div class="adm-muted adm-small">{{ $t->client_role }}{{ $t->client_company ? ' · '.$t->client_company : '' }}</div>
              </td>
              <td class="adm-small">{{ \Illuminate\Support\Str::limit($t->quote, 70) }}</td>
              <td>{{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}</td>
              <td><span class="adm-badge adm-badge--{{ $t->is_active ? 'contacted' : 'archived' }}">{{ $t->is_active ? 'active' : 'hidden' }}</span></td>
              <td class="adm-right"><a href="{{ route('admin.testimonials.edit', $t) }}" class="adm-btn adm-btn--sm">Edit</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
@endsection
