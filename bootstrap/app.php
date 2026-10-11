<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Models\Redirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->alias(['admin.only' => EnsureAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Admin-managed redirects (/admin/redirects) apply to ANY URL that
        // would otherwise 404 — multi-segment paths (/old/page), file-style
        // paths (/page.html) and renamed blog posts (/blog/old-slug)
        // included. Doing this at the 404 stage rather than in a catch-all
        // route means a real route or page can never be shadowed by a redirect.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
                return null;
            }

            try {
                $redirect = Redirect::where('from_path', Redirect::normalize('/'.ltrim($request->path(), '/')))
                    ->where('is_active', true)
                    ->first();
            } catch (\Throwable) {
                return null; // DB down: fall through to the normal 404 page.
            }

            if (! $redirect) {
                return null;
            }

            $redirect->increment('hits');

            return redirect($redirect->to_path, $redirect->status_code);
        });

        // An upload bigger than php.ini's post_max_size is rejected before the
        // session even starts (so no flash message is possible) and used to
        // end on a bare 413 page. Send admins back to the form with a marker
        // the admin layout turns into a readable message.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $back = $request->headers->get('referer');

            if (! $back || ! str_contains($back, '/admin')) {
                return null;
            }

            $back = preg_replace('/([?&])upload_too_large=1(&|$)/', '$1', $back);

            return redirect()->to(rtrim($back, '?&').(str_contains($back, '?') ? '&' : '?').'upload_too_large=1');
        });

        // The public contact form outliving its session (tab left open past
        // SESSION_LIFETIME) used to dead-end on a bare "419 Page Expired" and
        // lose the visitor's message. Send them back with everything they
        // typed and a clear prompt to resubmit instead.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419 || ! $request->routeIs('contact.store')) {
                return null;
            }

            return redirect()->route('contact')
                ->withInput($request->except(['_token', 'website', 'g-recaptcha-response']))
                ->with('contact_error', 'Your session timed out, so the message wasn’t sent. Everything you typed is still here — please press “Send message” again.')
                ->withFragment('contact-form');
        });
    })->create();
