<?php

namespace App\Http\Middleware;

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
 * A handful of well-known third-party domains are allowlisted unconditionally
 * (Google Analytics/Tag Manager, Meta Pixel, Google reCAPTCHA) so the
 * Settings > Integrations fields work the moment an admin fills them in,
 * without this middleware needing to know whether they're configured.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://www.google-analytics.com https://connect.facebook.net https://www.google.com https://www.gstatic.com",
            "style-src 'self' 'unsafe-inline' https://www.gstatic.com",
            "img-src 'self' data: https://www.google-analytics.com https://www.facebook.com",
            "font-src 'self'",
            "connect-src 'self' https://www.google-analytics.com https://analytics.google.com https://www.googletagmanager.com https://connect.facebook.net",
            "frame-src https://www.google.com",
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
}
