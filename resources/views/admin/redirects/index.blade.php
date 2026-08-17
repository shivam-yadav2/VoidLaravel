@extends('admin.layout')

@section('title', 'Redirects')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Redirects</h1>
      <p class="adm-muted">
        Sends visitors of an old URL to a new one instead of a 404. Only applies to URLs
        that don't match a real page, post, or route — you can't redirect away from a live page.
      </p>
    </div>
  </div>

  <div class="adm-detail">
    <div class="adm-detail__main">
      @if ($redirects->isEmpty())
        <div class="adm-empty"><p>No redirects yet.</p></div>
      @else
        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead><tr><th>From</th><th>To</th><th>Type</th><th>Hits</th><th>Status</th><th></th></tr></thead>
            <tbody>
              @foreach ($redirects as $r)
                <tr>
                  <td class="adm-small">{{ $r->from_path }}</td>
                  <td class="adm-small">{{ $r->to_path }}</td>
                  <td>{{ $r->status_code }}</td>
                  <td>{{ $r->hits }}</td>
                  <td>
                    <form method="POST" action="{{ route('admin.redirects.update', $r) }}" class="adm-inline-form">
                      @csrf
                      @method('PUT')
                      <input type="hidden" name="from_path" value="{{ $r->from_path }}" />
                      <input type="hidden" name="to_path" value="{{ $r->to_path }}" />
                      <input type="hidden" name="status_code" value="{{ $r->status_code }}" />
                      <label class="adm-check adm-check--inline">
                        <input type="checkbox" name="is_active" value="1" onchange="this.form.submit()" @checked($r->is_active) />
                        <span>{{ $r->is_active ? 'Active' : 'Off' }}</span>
                      </label>
                    </form>
                  </td>
                  <td class="adm-right">
                    <form method="POST" action="{{ route('admin.redirects.destroy', $r) }}"
                          onsubmit="return confirm('Delete this redirect?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="adm-btn adm-btn--sm adm-btn--danger">Delete</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    <aside class="adm-detail__side">
      <div class="adm-card">
        <h2 class="adm-label">Add redirect</h2>
        <form method="POST" action="{{ route('admin.redirects.store') }}">
          @csrf
          <div class="adm-field">
            <label>From path</label>
            <input type="text" name="from_path" value="{{ old('from_path') }}" placeholder="/old-page" required />
            @error('from_path')<span class="adm-field-error">{{ $message }}</span>@enderror
          </div>
          <div class="adm-field">
            <label>To</label>
            <input type="text" name="to_path" value="{{ old('to_path') }}" placeholder="/new-page or https://…" required />
          </div>
          <div class="adm-field">
            <label>Type</label>
            <select name="status_code">
              <option value="301" @selected(old('status_code', 301) == 301)>301 — Permanent</option>
              <option value="302" @selected(old('status_code') == 302)>302 — Temporary</option>
            </select>
          </div>
          <label class="adm-check">
            <input type="checkbox" name="is_active" value="1" checked />
            <span>Active</span>
          </label>
          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">Add redirect</button>
        </form>
      </div>
    </aside>
  </div>
@endsection
