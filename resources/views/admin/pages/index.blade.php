@extends('admin.layout')

@section('title', 'Pages')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Content pages</h1>
      <p class="adm-muted">Privacy policy, terms and any other standalone page.</p>
    </div>
    <a href="{{ route('admin.pages.create') }}" class="adm-btn adm-btn--primary">New page</a>
  </div>

  @if ($pages->isEmpty())
    <div class="adm-empty"><p>No pages yet.</p></div>
  @else
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr><th>Title</th><th>URL</th><th>Status</th><th>Updated</th><th></th></tr>
        </thead>
        <tbody>
          @foreach ($pages as $page)
            <tr>
              <td>
                <a href="{{ route('admin.pages.edit', $page) }}" class="adm-link">{{ $page->title }}</a>
                @if ($page->is_locked)
                  <span class="adm-badge adm-small" title="Required by the site">locked</span>
                @endif
              </td>
              <td class="adm-muted adm-small">/{{ $page->slug }}</td>
              <td>
                <span class="adm-badge adm-badge--{{ $page->is_published ? 'contacted' : 'archived' }}">
                  {{ $page->is_published ? 'published' : 'draft' }}
                </span>
              </td>
              <td class="adm-muted adm-small">{{ $page->updated_at->displayTz()->format('d M Y') }}</td>
              <td class="adm-right">
                <a href="{{ route('page.show', $page) }}" target="_blank" rel="noopener" class="adm-btn adm-btn--sm">View</a>
                <a href="{{ route('admin.pages.edit', $page) }}" class="adm-btn adm-btn--sm">Edit</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
@endsection
