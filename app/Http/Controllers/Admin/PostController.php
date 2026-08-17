<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $posts = Post::query()
            ->with('category')
            ->when(in_array($status, Post::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'status' => $status,
            'q' => $request->query('q'),
            'counts' => [
                'all' => Post::count(),
                'published' => Post::where('status', Post::STATUS_PUBLISHED)->count(),
                'draft' => Post::where('status', Post::STATUS_DRAFT)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', [
            'post' => new Post(['status' => Post::STATUS_DRAFT]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['user_id'] = $request->user()->id;

        if ($image = $this->storeImage($request)) {
            $data['featured_image'] = $image;
        }

        $post = Post::create($data);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('admin_success', 'Post created.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validated($request, $post);

        if ($request->boolean('remove_image') && $post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
            $data['featured_image'] = null;
        }

        if ($image = $this->storeImage($request)) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $image;
        }

        $post->update($data);

        return back()->with('admin_success', 'Post saved.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('admin_success', 'Post deleted.');
    }

    private function storeImage(Request $request): ?string
    {
        return $request->hasFile('featured_image')
            ? $request->file('featured_image')->store('blog', 'public')
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable', 'string', 'max:200', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('posts', 'slug')->ignore($post?->id),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', Rule::in(Post::STATUSES)],
            'published_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'read_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and hyphens.',
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);

        // The file itself is handled separately; keep it out of the mass update.
        unset($data['featured_image']);

        // Publishing without an explicit date should mean "now", and a post can
        // be scheduled by setting a future date.
        if ($data['status'] === Post::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
