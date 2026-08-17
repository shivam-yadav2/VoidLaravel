@extends('admin.layout')

@section('title', 'Sign in')

@section('content')
  <div class="adm-login">
    <div class="adm-login__card">
      <img src="{{ asset('assets/img/void_logo.png') }}" alt="Void Layer" class="adm-login__logo" />
      <h1>Admin sign in</h1>
      <p class="adm-login__sub">Manage enquiries from the website.</p>

      <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        @if ($errors->any())
          <div class="adm-alert adm-alert--error">{{ $errors->first() }}</div>
        @endif

        <div class="adm-field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus />
        </div>

        <div class="adm-field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required />
        </div>

        <label class="adm-check">
          <input type="checkbox" name="remember" value="1" />
          <span>Remember me</span>
        </label>

        <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">Sign in</button>
      </form>
    </div>
  </div>
@endsection
