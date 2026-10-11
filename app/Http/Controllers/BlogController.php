<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Support\Like;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $q = str((string) $request->query('q'))->squish()->limit(100, '')->toString();

        $posts = Post::published()
            ->with(['category', 'author'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', Like::contains($q))
                        ->orWhere('excerpt', 'like', Like::contains($q));
                });
            })
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $this->abortIfPastLastPage($posts);

        return view('pages.blog.index', [
            'posts' => $posts,
            'categories' => $this->categories(),
            'activeCategory' => null,
            'q' => $q,
            'seo' => [
                'key' => 'blog.index',
                'title' => 'Blog',
                'description' => 'Notes on custom software, ERP & HRMS, automation and shipping products — written by the Void Layer team.',
                'canonical' => $this->canonical(route('blog.index'), $posts),
                // Search result pages are thin, near-infinite duplicates.
                'noindex' => $q !== '',
                'breadcrumbs' => [['Home', route('home')], ['Blog', route('blog.index')]],
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

        $this->abortIfPastLastPage($posts);

        return view('pages.blog.index', [
            'posts' => $posts,
            'categories' => $this->categories(),
            'activeCategory' => $category,
            'q' => null,
            'seo' => [
                'title' => $category->name.' — Blog',
                'description' => $category->description ?: "Articles filed under {$category->name} from the Void Layer team.",
                'canonical' => $this->canonical(route('blog.category', $category), $posts),
                // An empty category is a thin page; keep it reachable but
                // out of the index until it has something in it.
                'noindex' => $posts->total() === 0,
                'breadcrumbs' => [
                    ['Home', route('home')],
                    ['Blog', route('blog.index')],
                    [$category->name, route('blog.category', $category)],
                ],
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

        $post->load(['category', 'author']);

        $breadcrumbs = [['Home', route('home')], ['Blog', route('blog.index')]];
        if ($post->category) {
            $breadcrumbs[] = [$post->category->name, route('blog.category', $post->category)];
        }
        $breadcrumbs[] = [$post->title, route('blog.show', $post)];

        return view('pages.blog.show', [
            'post' => $post,
            'related' => $related,
            'seo' => [
                'title' => $post->metaTitle(),
                'description' => $post->metaDescription(),
                'image' => $post->imageUrl() ? url($post->imageUrl()) : null,
                'type' => 'article',
                'published_time' => $post->published_at?->toIso8601String(),
                'modified_time' => $post->updated_at?->toIso8601String(),
                'author' => $post->author?->name,
                'breadcrumbs' => $breadcrumbs,
                'jsonld' => [
                    '@type' => 'BlogPosting',
                    'headline' => $post->title,
                    'description' => $post->metaDescription(),
                    'image' => $post->imageUrl() ? url($post->imageUrl()) : asset('assets/img/void_logo.png'),
                    'datePublished' => $post->published_at?->toIso8601String(),
                    'dateModified' => $post->updated_at?->toIso8601String(),
                    'author' => $post->author
                        ? ['@type' => 'Person', 'name' => $post->author->name]
                        : ['@id' => url('/').'#organization'],
                    'publisher' => ['@id' => url('/').'#organization'],
                    'mainEntityOfPage' => [
                        '@type' => 'WebPage',
                        '@id' => route('blog.show', $post),
                    ],
                ],
            ],
        ]);
    }

    private function categories()
    {
        return Category::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get();
    }

    /**
     * `?page=99` past the end used to render an empty 200 page — a soft 404
     * that search engines happily index.
     */
    private function abortIfPastLastPage(LengthAwarePaginator $posts): void
    {
        abort_if($posts->currentPage() > max(1, $posts->lastPage()), 404);
    }

    /**
     * Page 2+ is its own canonical URL (pointing it at page 1 tells search
     * engines to drop every post that only appears on later pages).
     */
    private function canonical(string $base, LengthAwarePaginator $posts): string
    {
        return $posts->currentPage() > 1 ? $base.'?page='.$posts->currentPage() : $base;
    }
}
