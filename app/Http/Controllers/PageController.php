<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Renders an admin-managed content page (privacy policy, terms, …), or
     * falls through to a configured redirect, or 404s. Bound last in the
     * route table — and takes a raw string slug rather than an implicit
     * `Page` binding — so a missing page can still fall through to the
     * redirects table instead of 404ing immediately.
     */
    public function show(string $slug): View|RedirectResponse
    {
        $page = Page::where('slug', $slug)->first();

        if ($page) {
            abort_unless($page->is_published, 404);

            return view('pages.page', [
                'page' => $page,
                'seo' => [
                    'title' => $page->metaTitle(),
                    'description' => $page->metaDescription(),
                ],
            ]);
        }

        $redirect = Redirect::where('from_path', Redirect::normalize($slug))
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            $redirect->increment('hits');

            return redirect($redirect->to_path, $redirect->status_code);
        }

        abort(404);
    }
}
