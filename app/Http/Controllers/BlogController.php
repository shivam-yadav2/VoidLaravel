<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Post::published()
            ->with(['category', 'author'])
            ->when($request->query('q'), function ($query, $term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('excerpt', 'like', "%{$term}%");
                });
            })
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('pages.blog.index', [
            'posts' => $posts,
            'categories' => Category::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get(),
            'activeCategory' => null,
            'q' => $request->query('q'),
            'seo' => [
                'key' => 'blog.index',
                'title' => 'Blog',
                'description' => 'Notes on custom software, ERP & HRMS, automation and shipping products — written by the Void Layer team.',
            ],
        ]);
    }

    public function category(Request $request, Category $category): View
    {
        $posts = Post::published()
            ->with(['category', 'author'])
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('pages.blog.index', [
            'posts' => $posts,
            'categories' => Category::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get(),
            'activeCategory' => $category,
            'q' => null,
            'seo' => [
                'title' => $category->name.' — Blog',
                'description' => $category->description ?: "Articles filed under {$category->name} from the Void Layer team.",
            ],
        ]);
    }

    public function show(Request $request, Post $post): View
    {
        abort_unless($post->isLive(), 404);

        // Count a view at most once per session per post.
        $seen = $request->session()->get('viewed_posts', []);
        if (! in_array($post->id, $seen, true)) {
            $post->increment('views');
            $request->session()->put('viewed_posts', [...$seen, $post->id]);
        }

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        // Backfill with any recent posts when the category is thin.
        if ($related->count() < 3) {
            $related = $related->concat(
                Post::published()
                    ->where('id', '!=', $post->id)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->latest('published_at')
                    ->take(3 - $related->count())
                    ->get()
            );
        }

        return view('pages.blog.show', [
            'post' => $post->load(['category', 'author']),
            'related' => $related,
            'seo' => [
                'title' => $post->metaTitle(),
                'description' => $post->metaDescription(),
                'image' => $post->imageUrl() ? url($post->imageUrl()) : null,
                'type' => 'article',
                'published_time' => $post->published_at?->toIso8601String(),
                'modified_time' => $post->updated_at?->toIso8601String(),
                'author' => $post->author?->name,
                'jsonld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'BlogPosting',
                    'headline' => $post->title,
                    'description' => $post->metaDescription(),
                    'image' => $post->imageUrl() ? url($post->imageUrl()) : asset('assets/img/void_logo.png'),
                    'datePublished' => $post->published_at?->toIso8601String(),
                    'dateModified' => $post->updated_at?->toIso8601String(),
                    'author' => [
                        '@type' => 'Person',
                        'name' => $post->author?->name ?? 'Void Layer',
                    ],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => 'Void Layer',
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => asset('assets/img/void_logo.png'),
                        ],
                    ],
                    'mainEntityOfPage' => [
                        '@type' => 'WebPage',
                        '@id' => route('blog.show', $post),
                    ],
                ],
            ],
        ]);
    }
}
