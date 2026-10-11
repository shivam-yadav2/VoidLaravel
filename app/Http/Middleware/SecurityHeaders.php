<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies to every response, public and admin alike. Values are deliberately
 * conservative rather than maximally strict: the theme relies on inline
 * <style="background-image:..."> attributes and a handful of inline <script>
 * blocks (hero headline animation, contact form, admin sidebar toggle), so a
 * nonce-free CSP has to allow 'unsafe-inline' for script/style or those break.
 * It still blocks the thing that actually matters after an XSS bug: loading
 * script/style/objects from an attacker-controlled external origin.
 *
 * Well-known third-party domains are allowlisted unconditionally (Google
 * Fonts, Google Analytics 4/Tag Manager — including its regional collection
 * endpoints — Meta Pixel, Google reCAPTCHA) so the Settings > Integrations
 * fields work the moment an admin fills them in. Anything else pasted into the
 * custom code fields (chat widgets, heatmaps, …) needs its origin listed in
 * Settings > Integrations > "Extra allowed domains".
 */
class SecurityHeaders
{
    private const FONTS_CSS = ['https://fonts.googleapis.com'];

    private const FONTS = ['https://fonts.gstatic.com'];

    private const ANALYTICS = [
        'https://www.googletagmanager.com',
        'https://*.googletagmanager.com',
        'https://www.google-analytics.com',
        'https://*.google-analytics.com',
        'https://*.analytics.google.com',
    ];

    private const META_PIXEL = ['https://connect.facebook.net', 'https://www.facebook.com'];

    private const RECAPTCHA = ['https://www.google.com', 'https://www.gstatic.com'];

    // Product demo videos (Admin > Products > video link).
    private const VIDEO_EMBEDS = ['https://www.youtube-nocookie.com', 'https://player.vimeo.com'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        $extra = self::extraDomains();

        $sources = fn (array ...$groups) => implode(' ', array_unique(array_merge(...[...$groups, $extra])));

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' ".$sources(self::ANALYTICS, self::META_PIXEL, self::RECAPTCHA),
            "style-src 'self' 'unsafe-inline' ".$sources(self::FONTS_CSS, ['https://www.gstatic.com']),
            "img-src 'self' data: ".$sources(self::ANALYTICS, self::META_PIXEL),
            "font-src 'self' data: ".$sources(self::FONTS),
            "connect-src 'self' ".$sources(self::ANALYTICS, self::META_PIXEL, self::RECAPTCHA),
            "frame-src ".$sources(self::RECAPTCHA, ['https://www.facebook.com'], self::VIDEO_EMBEDS),
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]));

        // Only sent once the site is actually served over HTTPS — sending it
        // over plain HTTP first would make browsers cache a redirect-to-HTTPS
        // rule for a site that might not have a working certificate yet.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Admin-supplied origins (Settings > Integrations), one per line or
     * space/comma separated. Only bare https:// origins (optionally with a
     * leading *. wildcard) are accepted, so a typo can't inject a directive.
     *
     * @return list<string>
     */
    public static function extraDomains(): array
    {
        $raw = (string) Setting::get('csp_extra_domains', '');

        return collect(preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($d) => rtrim(trim($d), '/'))
            ->filter(fn ($d) => preg_match('#^https://(\*\.)?[a-z0-9.-]+\.[a-z]{2,}(:\d+)?$#i', $d))
            ->unique()
            ->values()
            ->all();
    }
}
