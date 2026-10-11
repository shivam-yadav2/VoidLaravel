@extends('admin.layout')

@section('title', 'Blog')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Blog posts</h1>
      <p class="adm-muted">{{ $counts['all'] }} total · {{ $counts['published'] }} published · {{ $counts['draft'] }} draft</p>
    </div>
    <div class="adm-head-actions">
      <form method="GET" action="{{ route('admin.posts.index') }}" class="adm-search">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}" />@endif
        <input type="search" name="q" value="{{ $q }}" placeholder="Search titles…" />
        <button type="submit" class="adm-btn">Search</button>
      </form>
      <a href="{{ route('admin.posts.create') }}" class="adm-btn adm-btn--primary">New post</a>
    </div>
  </div>

  <div class="adm-tabs">
    @php
      $tabs = ['' => 'All ('.$counts['all'].')', 'published' => 'Published ('.$counts['published'].')', 'draft' => 'Drafts ('.$counts['draft'].')'];
    @endphp
    @foreach ($tabs as $value => $label)
      <a href="{{ route('admin.posts.index', array_filter(['status' => $value, 'q' => $q])) }}"
         class="adm-tab {{ (string) $status === (string) $value ? 'is-active' : '' }}">{{ $label }}</a>
    @endforeach
  </div>

  @if ($posts->isEmpty())
    <div class="adm-empty">
      <p>No posts {{ $q ? 'match that search' : 'yet' }}.</p>
      <p class="adm-muted">Write your first post to get the blog going.</p>
    </div>
  @else
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th>Views</th><th></th></tr>
        </thead>
        <tbody>
          @foreach ($posts as $post)
            <tr>
              <td>
                <a href="{{ route('admin.posts.edit', $post) }}" class="adm-link">{{ $post->title }}</a>
                <div class="adm-muted adm-small">/blog/{{ $post->slug }}</div>
              </td>
              <td>{{ $post->category?->name ?: '—' }}</td>
              <td>
                <span class="adm-badge adm-badge--{{ $post->status === 'published' ? 'contacted' : 'archived' }}">{{ $post->status }}</span>
                @if ($post->status === 'published' && ! $post->isLive())
                  <span class="adm-badge adm-badge--new">scheduled</span>
                @endif
              </td>
              <td class="adm-muted adm-small">{{ $post->published_at?->displayTz()->format('d M Y') ?: '—' }}</td>
              <td class="adm-muted adm-small">{{ $post->views }}</td>
              <td class="adm-right">
                @if ($post->isLive())
                  <a href="{{ route('blog.show', $post) }}" target="_blank" rel="noopener" class="adm-btn adm-btn--sm">View</a>
                @endif
                <a href="{{ route('admin.posts.edit', $post) }}" class="adm-btn adm-btn--sm">Edit</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="adm-pagination">{{ $posts->links() }}</div>
  @endif
@endsection
