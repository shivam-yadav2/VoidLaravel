<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\Like;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public const IMAGE_KB = 4096;
    public const VIDEO_KB = 51200;
    public const MAX_GALLERY = 12;

    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q'));

        return view('admin.products.index', [
            'products' => Product::query()
                ->withCount('images')
                ->when($term !== '', fn ($q) => $q->where('name', 'like', Like::contains($term)))
                ->ordered()
                ->get(),
            'q' => $term,
            'counts' => ['all' => Product::count(), 'published' => Product::published()->count()],
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product(['status' => 'live', 'is_published' => false])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $product = Product::create($data + $this->storeFiles($request));
        $this->addGallery($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('admin_success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product->load('images')]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);

        foreach (['logo' => 'remove_logo', 'cover_image' => 'remove_cover', 'video_path' => 'remove_video'] as $column => $flag) {
            if ($request->boolean($flag) && $product->{$column}) {
                Storage::disk('public')->delete($product->{$column});
                $data[$column] = null;
            }
        }

        foreach ($this->storeFiles($request) as $column => $path) {
            if ($product->{$column}) {
                Storage::disk('public')->delete($product->{$column});
            }
            $data[$column] = $path;
        }

        $product->update($data);

        // Existing gallery: alt text, order, removals.
        $remove = array_map('intval', (array) $request->input('remove_images', []));
        foreach ($product->images as $image) {
            if (in_array($image->id, $remove, true)) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
                continue;
            }
            $image->update([
                'alt' => $request->input("gallery_alt.{$image->id}", $image->alt),
                'sort_order' => (int) $request->input("gallery_order.{$image->id}", $image->sort_order),
            ]);
        }

        $this->addGallery($request, $product);

        return back()->with('admin_success', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach (array_filter([$product->logo, $product->cover_image, $product->video_path]) as $path) {
            Storage::disk('public')->delete($path);
        }
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete(); // gallery rows cascade

        return redirect()->route('admin.products.index')->with('admin_success', 'Product deleted.');
    }

    /** @return array<string, string> newly stored file paths by column */
    private function storeFiles(Request $request): array
    {
        $paths = [];
        foreach (['logo' => 'logo', 'cover_image' => 'cover_image', 'video_file' => 'video_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                $paths[$column] = $request->file($input)->store('products', 'public');
            }
        }

        return $paths;
    }

    private function addGallery(Request $request, Product $product): void
    {
        $next = (int) $product->images()->max('sort_order') + 1;
        foreach ($request->file('gallery', []) as $file) {
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $file->store('products/gallery', 'public'),
                'sort_order' => $next++,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $existing = $product?->images()->count() ?? 0;
        $removing = count((array) $request->input('remove_images', []));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'regex:/^[a-z0-9-]+$/', Rule::unique('products', 'slug')->ignore($product?->id)],
            'tagline' => ['nullable', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(array_keys(Product::STATUSES))],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'string', 'max:3000'],
            'tech_stack' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:'.self::IMAGE_KB],
            'cover_image' => ['nullable', 'image', 'max:'.self::IMAGE_KB],
            'cover_image_alt' => ['nullable', 'string', 'max:255'],
            'gallery' => ['nullable', 'array', 'max:'.max(0, self::MAX_GALLERY - $existing + $removing)],
            'gallery.*' => ['image', 'max:'.self::IMAGE_KB],
            'gallery_alt' => ['nullable', 'array'],
            'gallery_alt.*' => ['nullable', 'string', 'max:255'],
            'gallery_order' => ['nullable', 'array'],
            'gallery_order.*' => ['nullable', 'integer', 'min:0'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:'.self::VIDEO_KB],
            'video_url' => ['nullable', 'url', 'max:255', function ($attr, $value, $fail) {
                if ($value && ! Product::embedUrl($value)) {
                    $fail('Use a YouTube or Vimeo link (e.g. https://youtu.be/… or https://vimeo.com/…).');
                }
            }],
            'product_url' => ['nullable', 'url', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and hyphens.',
            'gallery.max' => 'A product can have up to '.self::MAX_GALLERY.' gallery images.',
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');

        // Files and gallery bookkeeping are handled separately.
        foreach (['logo', 'cover_image', 'video_file', 'gallery', 'gallery_alt', 'gallery_order'] as $key) {
            unset($data[$key]);
        }

        return $data;
    }
}
