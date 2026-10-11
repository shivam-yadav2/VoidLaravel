@extends('admin.layout')

@section('title', 'Lead · '.$lead->name)

@section('content')
  <a href="{{ route('admin.leads.index') }}" class="adm-back">&larr; Back to leads</a>

  <div class="adm-detail">
    <div class="adm-detail__main">
      <div class="adm-card">
        <div class="adm-detail__head">
          <div>
            <h1>{{ $lead->name }}</h1>
            <p class="adm-muted">
              {{ $lead->created_at->displayTz()->format('d M Y, H:i T') }} · {{ $lead->created_at->diffForHumans() }}
            </p>
          </div>
          <span class="adm-badge adm-badge--{{ $lead->status }}">{{ $lead->status }}</span>
        </div>

        <h2 class="adm-label">Brief</h2>
        <p class="adm-brief">{{ $lead->brief }}</p>
      </div>
    </div>

    <aside class="adm-detail__side">
      <div class="adm-card">
        <h2 class="adm-label">Contact</h2>
        <dl class="adm-dl">
          <dt>Email</dt>
          <dd><a href="mailto:{{ $lead->email }}" class="adm-link">{{ $lead->email }}</a></dd>
          <dt>Company</dt>
          <dd>{{ $lead->company ?: '—' }}</dd>
          <dt>Budget</dt>
          <dd>{{ $lead->budget ?: '—' }}</dd>
          <dt>IP</dt>
          <dd class="adm-muted adm-small">{{ $lead->ip_address ?: '—' }}</dd>
        </dl>

        <a href="mailto:{{ $lead->email }}?subject={{ rawurlencode('Re: your enquiry — Void Layer') }}"
           class="adm-btn adm-btn--primary adm-btn--block">Reply by email</a>
      </div>

      <div class="adm-card">
        <h2 class="adm-label">Status</h2>
        <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="adm-status-form">
          @csrf
          @method('PATCH')
          @foreach (\App\Models\Lead::STATUSES as $s)
            <button
              type="submit"
              name="status"
              value="{{ $s }}"
              class="adm-btn adm-btn--block {{ $lead->status === $s ? 'is-current' : '' }}"
              @disabled($lead->status === $s)
            >{{ ucfirst($s) }}</button>
          @endforeach
        </form>
      </div>

      <div class="adm-card">
        <h2 class="adm-label">Danger zone</h2>
        <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}"
              onsubmit="return confirm('Delete this lead permanently? This cannot be undone.');">
          @csrf
          @method('DELETE')
          <button type="submit" class="adm-btn adm-btn--danger adm-btn--block">Delete lead</button>
        </form>
      </div>
    </aside>
  </div>
@endsection
