@extends('admin.layout')

@section('title', 'Security')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Security</h1>
      <p class="adm-muted">Two-factor authentication for your own account, {{ $user->email }}.</p>
    </div>
  </div>

  @if (session('recovery_codes'))
    <div class="adm-card adm-recovery-card">
      <h2 class="adm-label">Save these recovery codes now</h2>
      <p class="adm-muted">
        Each code works once, if you ever lose access to your authenticator app. This is the only
        time they're shown — store them somewhere safe (a password manager, not a screenshot on the same phone).
      </p>
      <div class="adm-recovery-grid">
        @foreach (session('recovery_codes') as $code)
          <code>{{ $code }}</code>
        @endforeach
      </div>
    </div>
  @endif

  @if ($user->hasTwoFactorEnabled())
    <div class="adm-card">
      <h2 class="adm-label">Two-factor authentication</h2>
      <p><span class="adm-badge adm-badge--contacted">Enabled</span> since {{ $user->two_factor_confirmed_at->displayTz()->format('d M Y') }}.</p>

      <form method="POST" action="{{ route('admin.security.2fa.recovery-codes') }}" class="adm-inline-security-form">
        @csrf
        <input type="password" name="password" placeholder="Current password" required />
        <button type="submit" class="adm-btn">Regenerate recovery codes</button>
      </form>

      <form method="POST" action="{{ route('admin.security.2fa.disable') }}" class="adm-inline-security-form">
        @csrf
        <input type="password" name="password" placeholder="Current password" required />
        <button type="submit" class="adm-btn adm-btn--danger"
                onclick="return confirm('Disable two-factor authentication?');">Disable 2FA</button>
      </form>
      @error('password')<span class="adm-field-error">{{ $message }}</span>@enderror
    </div>
  @elseif ($pendingSecret)
    <div class="adm-card">
      <h2 class="adm-label">Finish setting up two-factor authentication</h2>
      <ol class="adm-setup-steps">
        <li>Open your authenticator app (Google Authenticator, Authy, 1Password, …).</li>
        <li>Add an account using this manual entry key:
          <div class="adm-secret-key">{{ chunk_split($pendingSecret, 4, ' ') }}</div>
        </li>
        <li>Enter the 6-digit code it shows below to confirm.</li>
      </ol>

      <form method="POST" action="{{ route('admin.security.2fa.confirm') }}" class="adm-confirm-form">
        @csrf
        <input type="text" name="code" inputmode="numeric" placeholder="123456" autofocus required />
        <button type="submit" class="adm-btn adm-btn--primary">Confirm & enable</button>
      </form>
      @error('code')<span class="adm-field-error">{{ $message }}</span>@enderror

      <form method="POST" action="{{ route('admin.security.2fa.enable') }}">
        @csrf
        <button type="submit" class="adm-link adm-small">Generate a different key</button>
      </form>
    </div>
  @else
    <div class="adm-card">
      <h2 class="adm-label">Two-factor authentication</h2>
      <p class="adm-muted">
        <span class="adm-badge">Off</span> Add a second step at login using an authenticator app,
        so a leaked password alone isn't enough to get in.
      </p>
      <form method="POST" action="{{ route('admin.security.2fa.enable') }}">
        @csrf
        <button type="submit" class="adm-btn adm-btn--primary">Set up two-factor authentication</button>
      </form>
    </div>
  @endif
@endsection
