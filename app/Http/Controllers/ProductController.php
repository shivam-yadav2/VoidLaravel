<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::published()->with('images')->ordered()->get();

        return view('pages.products.index', [
            'products' => $products,
            'seo' => [
                'key' => 'products.index',
                'title' => 'Our Products',
                'description' => 'SaaS products and tools designed, built and run by Void Layer.',
                'canonical' => route('products.index'),
                'noindex' => $products->isEmpty(),
                'breadcrumbs' => [['Home', route('home')], ['Products', route('products.index')]],
            ],
        ]);
    }

    /**
     * Standalone detail page. On the site, cards open the same content in a
     * modal; this URL is what gets shared, indexed, and used without JS.
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_published, 404);

        $product->load('images');

        return view('pages.products.show', [
            'product' => $product,
            'more' => Product::published()->with('images')->whereKeyNot($product->id)->ordered()->take(3)->get(),
            'seo' => [
                'title' => $product->metaTitle(),
                'description' => $product->metaDescription(),
                'image' => $product->coverUrl() ? url($product->coverUrl()) : null,
                'breadcrumbs' => [
                    ['Home', route('home')],
                    ['Products', route('products.index')],
                    [$product->name, route('products.show', $product)],
                ],
                'jsonld' => array_filter([
                    '@type' => 'SoftwareApplication',
                    'name' => $product->name,
                    'description' => $product->metaDescription(),
                    'applicationCategory' => $product->category ?: 'BusinessApplication',
                    'image' => $product->coverUrl() ? url($product->coverUrl()) : null,
                    'url' => $product->product_url ?: route('products.show', $product),
                    'creator' => ['@id' => url('/').'#organization'],
                ]),
            ],
        ]);
    }
}
