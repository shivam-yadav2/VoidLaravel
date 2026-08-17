<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to the 'admin' role. Editors can manage day-to-day
 * content (leads, blog, pages, homepage sections) but not site-wide settings,
 * SEO, other user accounts, or redirects — those can break the whole site or
 * grant further access, so they stay admin-only.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'This section is restricted to admin accounts.');

        return $next($request);
    }
}
