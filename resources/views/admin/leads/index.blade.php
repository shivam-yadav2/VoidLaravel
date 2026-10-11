@extends('admin.layout')

@section('title', 'Leads')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Leads</h1>
      <p class="adm-muted">
        {{ $counts['all'] }} total · {{ $counts['unread'] }} unread
      </p>
    </div>

    <div class="adm-head-actions">
      <form method="GET" action="{{ route('admin.leads.index') }}" class="adm-search">
        @if ($status)
          <input type="hidden" name="status" value="{{ $status }}" />
        @endif
        <input type="search" name="q" value="{{ $q }}" placeholder="Search name, email, company…" />
        <button type="submit" class="adm-btn">Search</button>
      </form>
      <a href="{{ route('admin.leads.export', request()->only('status', 'q')) }}" class="adm-btn">Export CSV</a>
    </div>
  </div>

  <div class="adm-tabs">
    @php
      $tabs = [
        '' => 'All ('.$counts['all'].')',
        'new' => 'New ('.$counts['new'].')',
        'contacted' => 'Contacted ('.$counts['contacted'].')',
        'archived' => 'Archived ('.$counts['archived'].')',
      ];
    @endphp
    @foreach ($tabs as $value => $label)
      <a
        href="{{ route('admin.leads.index', array_filter(['status' => $value, 'q' => $q])) }}"
        class="adm-tab {{ (string) $status === (string) $value ? 'is-active' : '' }}"
      >{{ $label }}</a>
    @endforeach
  </div>

  @if ($leads->isEmpty())
    <div class="adm-empty">
      <p>No leads {{ $q ? 'match that search' : 'yet' }}.</p>
      <p class="adm-muted">Submissions from the website contact form land here.</p>
    </div>
  @else
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Company</th>
            <th>Budget</th>
            <th>Status</th>
            <th>Received</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($leads as $lead)
            <tr class="{{ $lead->isUnread() ? 'is-unread' : '' }}">
              <td>
                <a href="{{ route('admin.leads.show', $lead) }}" class="adm-link">
                  @if ($lead->isUnread())<span class="adm-dot" title="Unread"></span>@endif
                  {{ $lead->name }}
                </a>
                <div class="adm-muted adm-small">{{ $lead->email }}</div>
              </td>
              <td>{{ $lead->company ?: '—' }}</td>
              <td>{{ $lead->budget ?: '—' }}</td>
              <td><span class="adm-badge adm-badge--{{ $lead->status }}">{{ $lead->status }}</span></td>
              <td class="adm-muted adm-small">{{ $lead->created_at->displayTz()->format('d M Y, H:i') }}</td>
              <td class="adm-right">
                <a href="{{ route('admin.leads.show', $lead) }}" class="adm-btn adm-btn--sm">View</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="adm-pagination">{{ $leads->links() }}</div>
  @endif
@endsection
