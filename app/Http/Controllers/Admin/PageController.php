<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::orderBy('title')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new Page(['is_published' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $page = Page::create($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('admin_success', 'Page created.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validated($request, $page);

        // Locked pages (privacy, terms) keep their slug so footer links and
        // any indexed URLs never break.
        if ($page->is_locked) {
            unset($data['slug']);
        }

        $page->update($data);

        return back()->with('admin_success', 'Page saved.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        if ($page->is_locked) {
            return back()->with('admin_error', 'This page is required by the site and cannot be deleted.');
        }

        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('admin_success', 'Page deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Page $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable', 'string', 'max:200', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('pages', 'slug')->ignore($page?->id),
            ],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and hyphens.',
        ]);

        // A disabled slug input (locked pages) is not submitted at all, so the
        // key can be missing entirely rather than merely blank.
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
