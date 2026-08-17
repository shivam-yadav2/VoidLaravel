<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('services'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => route('about'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('contact'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('blog.index'), 'priority' => '0.9', 'changefreq' => 'weekly'],
        ];

        foreach (Post::published()->latest('published_at')->get() as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post),
                'lastmod' => $post->updated_at?->toAtomString(),
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ];
        }

        foreach (Category::has('posts')->get() as $category) {
            $urls[] = [
                'loc' => route('blog.category', $category),
                'priority' => '0.5',
                'changefreq' => 'monthly',
            ];
        }

        foreach (Page::published()->get() as $page) {
            $urls[] = [
                'loc' => route('page.show', $page),
                'lastmod' => $page->updated_at?->toAtomString(),
                'priority' => '0.3',
                'changefreq' => 'yearly',
            ];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
