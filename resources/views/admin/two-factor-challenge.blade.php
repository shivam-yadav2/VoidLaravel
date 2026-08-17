@extends('admin.layout')

@section('title', 'Verify it\'s you')

@section('content')
  <div class="adm-login">
    <div class="adm-login__card">
      <img src="{{ asset('assets/img/void_logo.png') }}" alt="{{ $site->site_name }}" class="adm-login__logo" />
      <h1>Two-factor verification</h1>
      <p class="adm-login__sub">Enter the 6-digit code from your authenticator app, or one of your recovery codes.</p>

      <form method="POST" action="{{ route('admin.2fa.verify') }}">
        @csrf

        @if ($errors->any())
          <div class="adm-alert adm-alert--error">{{ $errors->first() }}</div>
        @endif

        <div class="adm-field">
          <label for="code">Code</label>
          <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                 placeholder="123456" autofocus required />
        </div>

        <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">Verify</button>
      </form>

      <p class="adm-login__cancel">
        <a href="{{ route('login') }}" class="adm-link">&larr; Back to login</a>
      </p>
    </div>
  </div>
@endsection
