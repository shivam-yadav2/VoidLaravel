<?php

namespace App\Rules;

use App\Support\SiteConfig;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request as RequestFacade;

/**
 * Verifies a Google reCAPTCHA v2 token server-side. Only ever added to the
 * validation rules when Settings > Integrations has both keys filled in
 * (see ContactController) — this rule assumes it's configured and fails
 * closed if the secret is somehow missing, rather than silently passing.
 */
class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = app(SiteConfig::class)->recaptcha_secret_key;

        if (! $secret) {
            $fail('reCAPTCHA is not configured correctly. Contact the site owner.');

            return;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $value,
            'remoteip' => RequestFacade::ip(),
        ]);

        if (! $response->successful() || ! ($response->json('success') ?? false)) {
            $fail('Please confirm you are not a robot.');
        }
    }
}
