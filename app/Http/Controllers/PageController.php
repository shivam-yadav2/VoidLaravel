<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Renders an admin-managed content page (privacy policy, terms, …), or
     * 404s. Bound last in the route table so a page slug can never shadow a
     * real route. A 404 from here (or from anywhere else) still gets a chance
     * to match the admin-managed redirects table — see bootstrap/app.php.
     */
    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)->first();

        abort_unless($page && $page->is_published, 404);

        return view('pages.page', [
            'page' => $page,
            'seo' => [
                'title' => $page->metaTitle(),
                'description' => $page->metaDescription(),
                'modified_time' => $page->updated_at?->toIso8601String(),
                'breadcrumbs' => [['Home', route('home')], [$page->title, route('page.show', $page)]],
            ],
        ]);
    }
}
