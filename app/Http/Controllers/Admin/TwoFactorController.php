<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    /**
     * Shows current 2FA status, and — if a setup is in progress — the
     * manual entry key and confirmation form.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $pendingSecret = $request->session()->get('2fa-setup-secret');

        return view('admin.security', [
            'user' => $user,
            'pendingSecret' => $pendingSecret,
            'otpAuthUri' => $pendingSecret
                ? Totp::otpAuthUri($pendingSecret, $user->email, config('app.name').' Admin')
                : null,
        ]);
    }

    /**
     * Starts (or restarts) setup: generates a new secret and stashes it in
     * the session — deliberately NOT written to the user record yet, so an
     * abandoned setup can never leave a half-configured, unusable state on
     * the account.
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->session()->put('2fa-setup-secret', Totp::generateSecret());

        return redirect()->route('admin.security');
    }

    /**
     * Confirms setup: the user must prove they can generate a valid code
     * from the pending secret before it's activated. Also issues one-time
     * recovery codes, shown exactly once.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $secret = $request->session()->get('2fa-setup-secret');

        abort_unless($secret, 400, 'No pending 2FA setup. Start again.');

        $request->validate(['code' => ['required', 'string']]);

        if (! Totp::verify($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'That code is incorrect. Check your authenticator app and try again.']);
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
            'two_factor_last_step' => null,
        ])->save();

        $request->session()->forget('2fa-setup-secret');
        $request->session()->flash('recovery_codes', $recoveryCodes);

        return redirect()->route('admin.security')->with('admin_success', 'Two-factor authentication is now on.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_step' => null,
        ])->save();

        return redirect()->route('admin.security')->with('admin_success', 'Two-factor authentication is now off.');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        abort_unless($request->user()->hasTwoFactorEnabled(), 400);

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();

        $request->user()->forceFill(['two_factor_recovery_codes' => $recoveryCodes])->save();

        $request->session()->flash('recovery_codes', $recoveryCodes);

        return redirect()->route('admin.security')->with('admin_success', 'New recovery codes generated — save them now, the old ones no longer work.');
    }
}
