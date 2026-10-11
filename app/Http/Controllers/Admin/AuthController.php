<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    /**
     * Checks credentials but deliberately does NOT call Auth::login() yet
     * when the account has 2FA enabled — Auth::validate() only verifies the
     * password, it never establishes a session. That means a partially
     * authenticated request never has an active guard session to smuggle
     * anything through; the account is either fully logged in (no 2FA) or
     * not logged in at all (pending 2FA), with no state in between.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Those credentials do not match our records.',
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->regenerate();
            $request->session()->put('2fa.user.id', $user->id);
            $request->session()->put('2fa.remember', $request->boolean('remember'));

            return redirect()->route('admin.2fa.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function showChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('2fa.user.id')) {
            return redirect()->route('login');
        }

        return view('admin.two-factor-challenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa.user.id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate(['code' => ['required', 'string']]);

        $user = User::find($userId);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['2fa.user.id', '2fa.remember']);

            return redirect()->route('login');
        }

        $code = trim($request->input('code'));

        $step = Totp::matchedStep($user->two_factor_secret, $code);

        // A code is only good once: reject a step at or before the last one
        // used, so a code shoulder-surfed or phished mid-login can't be
        // replayed within its 90-second validity window.
        if ($step !== null && ($user->two_factor_last_step === null || $step > $user->two_factor_last_step)) {
            $user->forceFill(['two_factor_last_step' => $step])->save();

            return $this->completeTwoFactorLogin($request, $user);
        }

        if ($this->consumeRecoveryCode($user, $code)) {
            return $this->completeTwoFactorLogin($request, $user);
        }

        throw ValidationException::withMessages([
            'code' => 'That code is incorrect. Check your authenticator app, or use a recovery code.',
        ]);
    }

    private function completeTwoFactorLogin(Request $request, User $user): RedirectResponse
    {
        $remember = $request->session()->pull('2fa.remember', false);
        $request->session()->forget('2fa.user.id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    private function consumeRecoveryCode(User $user, string $submitted): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $submitted = Str::upper(trim($submitted));

        if (! in_array($submitted, $codes, true)) {
            return false;
        }

        // Single use — remove it so the same code can't be replayed.
        $user->forceFill([
            'two_factor_recovery_codes' => array_values(array_diff($codes, [$submitted])),
        ])->save();

        return true;
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
