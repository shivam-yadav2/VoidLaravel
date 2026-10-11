@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Dashboard</h1>
      <p class="adm-muted">{{ now()->displayTz()->format('l, d F Y') }}</p>
    </div>
  </div>

  <div class="adm-stat-grid">
    <div class="adm-stat-card">
      <span class="adm-stat-card__label">Leads this week</span>
      <span class="adm-stat-card__value">{{ $leadsThisWeek }}</span>
    </div>
    <div class="adm-stat-card">
      <span class="adm-stat-card__label">Unread leads</span>
      <span class="adm-stat-card__value">{{ $unreadLeads }}</span>
    </div>
    <div class="adm-stat-card">
      <span class="adm-stat-card__label">Total leads</span>
      <span class="adm-stat-card__value">{{ $totalLeads }}</span>
    </div>
    <div class="adm-stat-card">
      <span class="adm-stat-card__label">Published posts</span>
      <span class="adm-stat-card__value">{{ $publishedPosts }}</span>
    </div>
    <div class="adm-stat-card">
      <span class="adm-stat-card__label">Draft posts</span>
      <span class="adm-stat-card__value">{{ $draftPosts }}</span>
    </div>
  </div>

  <div class="adm-quick-actions">
    <a href="{{ route('admin.posts.create') }}" class="adm-btn adm-btn--primary">Write a post</a>
    <a href="{{ route('admin.leads.index') }}" class="adm-btn">View leads</a>
    <a href="{{ route('admin.leads.export') }}" class="adm-btn">Export leads CSV</a>
  </div>

  <div class="adm-detail">
    <div class="adm-detail__main">
      <div class="adm-card">
        <div class="adm-page-head">
          <h2>Recent leads</h2>
          <a href="{{ route('admin.leads.index') }}" class="adm-link adm-small">View all &rarr;</a>
        </div>

        @if ($recentLeads->isEmpty())
          <p class="adm-muted">No leads yet.</p>
        @else
          <div class="adm-table-wrap">
            <table class="adm-table">
              <thead><tr><th>Name</th><th>Status</th><th>Received</th></tr></thead>
              <tbody>
                @foreach ($recentLeads as $lead)
                  <tr class="{{ $lead->isUnread() ? 'is-unread' : '' }}">
                    <td>
                      <a href="{{ route('admin.leads.show', $lead) }}" class="adm-link">{{ $lead->name }}</a>
                      <div class="adm-muted adm-small">{{ $lead->email }}</div>
                    </td>
                    <td><span class="adm-badge adm-badge--{{ $lead->status }}">{{ $lead->status }}</span></td>
                    <td class="adm-muted adm-small">{{ $lead->created_at->diffForHumans() }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>

    <aside class="adm-detail__side">
      <div class="adm-card">
        <h2 class="adm-label">Top blog posts</h2>
        @if ($topPosts->isEmpty())
          <p class="adm-muted">No published posts yet.</p>
        @else
          <ul class="adm-simple-list">
            @foreach ($topPosts as $post)
              <li>
                <a href="{{ route('admin.posts.edit', $post) }}" class="adm-link">{{ $post->title }}</a>
                <span class="adm-muted adm-small">{{ $post->views }} views</span>
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    </aside>
  </div>
@endsection
