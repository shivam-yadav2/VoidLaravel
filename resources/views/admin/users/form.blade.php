@extends('admin.layout')

@section('title', $user->exists ? 'Edit team member' : 'New team member')

@section('content')
  <a href="{{ route('admin.users.index') }}" class="adm-back">&larr; Back to team</a>

  <h1>{{ $user->exists ? 'Edit team member' : 'New team member' }}</h1>

  <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf
    @if ($user->exists) @method('PUT') @endif

    <div class="adm-card">
      <div class="adm-grid-2">
        <div class="adm-field">
          <label>Name</label>
          <input type="text" name="name" value="{{ old('name', $user->name) }}" required />
        </div>
        <div class="adm-field">
          <label>Email</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" required />
        </div>
        <div class="adm-field">
          <label>Password</label>
          <input type="password" name="password" placeholder="{{ $user->exists ? 'Leave blank to keep current password' : '' }}" {{ $user->exists ? '' : 'required' }} />
        </div>
        <div class="adm-field">
          <label>Role</label>
          <select name="role">
            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin — full access</option>
            <option value="editor" @selected(old('role', $user->role) === 'editor')>Editor — leads, blog, pages, homepage content</option>
          </select>
        </div>
      </div>
    </div>

    <button type="submit" class="adm-btn adm-btn--primary">
      {{ $user->exists ? 'Save' : 'Add team member' }}
    </button>
  </form>

  @if ($user->exists && ! $user->is(auth()->user()))
    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="adm-danger-form"
          onsubmit="return confirm('Remove this team member? They will lose access immediately.');">
      @csrf
      @method('DELETE')
      <button type="submit" class="adm-btn adm-btn--danger">Remove team member</button>
    </form>
  @endif
@endsection
