@extends('admin.layout')

@section('title', 'SEO')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Meta tags</h1>
      <p class="adm-muted">
        Override the title, description and social image for each fixed page.
        Blog posts and content pages have their own SEO fields on their edit screens.
      </p>
    </div>
  </div>

  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Page</th>
          <th>Meta title</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($pages as $key => $page)
          @php $record = $records[$key] ?? null; @endphp
          <tr>
            <td><strong>{{ $page['label'] }}</strong>
              <div class="adm-muted adm-small">{{ route($page['route']) }}</div>
            </td>
            <td>{{ $record?->meta_title ?: '—' }}</td>
            <td>
              @if ($record?->noindex)
                <span class="adm-badge adm-badge--archived">noindex</span>
              @elseif ($record && ($record->meta_title || $record->meta_description))
                <span class="adm-badge adm-badge--contacted">customised</span>
              @else
                <span class="adm-badge">default</span>
              @endif
            </td>
            <td class="adm-right">
              <a href="{{ route('admin.seo.edit', $key) }}" class="adm-btn adm-btn--sm">Edit</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
