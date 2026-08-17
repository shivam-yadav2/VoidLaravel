@extends('admin.layout')

@section('title', 'Team')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Team accounts</h1>
      <p class="adm-muted">Admins manage everything. Editors can't touch Settings, SEO, or other accounts.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="adm-btn adm-btn--primary">Add team member</a>
  </div>

  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr></thead>
      <tbody>
        @foreach ($users as $user)
          <tr>
            <td>
              <a href="{{ route('admin.users.edit', $user) }}" class="adm-link">{{ $user->name }}</a>
              @if ($user->is(auth()->user()))<span class="adm-badge adm-small">you</span>@endif
            </td>
            <td class="adm-small">{{ $user->email }}</td>
            <td><span class="adm-badge adm-badge--{{ $user->isAdmin() ? 'contacted' : 'new' }}">{{ $user->role }}</span></td>
            <td class="adm-right"><a href="{{ route('admin.users.edit', $user) }}" class="adm-btn adm-btn--sm">Edit</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
