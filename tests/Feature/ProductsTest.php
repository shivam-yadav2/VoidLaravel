<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create([
            'name' => 'Attendly',
            'slug' => 'attendly',
            'tagline' => 'Attendance & payroll for small factories',
            'category' => 'SaaS',
            'status' => 'live',
            'summary' => 'Shift attendance, leave and payroll in one place.',
            'features' => "Biometric sync\nPayroll in one click\n\n  Leave approvals  ",
            'tech_stack' => 'Laravel, React',
            'product_url' => 'https://attendly.example.com',
            'is_published' => true,
            ...$attrs,
        ]);
    }

    public function test_homepage_section_is_hidden_until_a_product_is_published(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="products"', false);

        $this->product(['is_published' => false]);
        $this->get('/')->assertDontSee('id="products"', false);

        Product::query()->update(['is_published' => true]);
        $this->get('/')->assertSee('id="products"', false)->assertSee('Attendly');
    }

    public function test_cards_link_to_the_detail_page_and_ship_a_modal_template(): void
    {
        $this->product();

        $this->get('/products')
            ->assertOk()
            ->assertSee('href="'.route('products.show', 'attendly').'"', false)
            ->assertSee('data-product-open="attendly"', false)
            ->assertSee('<template id="product-tpl-attendly">', false)
            ->assertSee('data-product-modal', false)
            ->assertSee('Payroll in one click');
    }

    public function test_detail_page_shows_everything_and_drafts_404(): void
    {
        $this->product();
        $this->product(['name' => 'Secret', 'slug' => 'secret', 'is_published' => false]);

        $this->get('/products/attendly')
            ->assertOk()
            ->assertSee('<h1 class="pd-name"', false)
            ->assertSee('Leave approvals')
            ->assertSee('https://attendly.example.com', false)
            ->assertSee('"@type":"SoftwareApplication"', false);

        $this->get('/products/secret')->assertNotFound();
    }

    public function test_feature_list_trims_and_skips_blank_lines(): void
    {
        $this->assertSame(['Biometric sync', 'Payroll in one click', 'Leave approvals'], $this->product()->featureList());
    }

    public function test_video_links_become_privacy_friendly_embeds(): void
    {
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0&modestbranding=1&playsinline=1', Product::embedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10'));
        $this->assertStringContainsString('/embed/dQw4w9WgXcQ', Product::embedUrl('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertStringContainsString('/embed/dQw4w9WgXcQ', Product::embedUrl('https://youtube.com/shorts/dQw4w9WgXcQ'));
        $this->assertSame('https://player.vimeo.com/video/76979871?dnt=1', Product::embedUrl('https://vimeo.com/76979871'));
        $this->assertNull(Product::embedUrl('https://example.com/video.mp4'));
    }

    public function test_csp_allows_the_video_embeds(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://www.youtube-nocookie.com', $csp);
        $this->assertStringContainsString('https://player.vimeo.com', $csp);
    }

    public function test_editor_creates_a_product_with_media_and_manages_the_gallery(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->actingAs($editor)->post('/admin/products', [
            'name' => 'Stocky',
            'status' => 'beta',
            'summary' => 'Inventory for small shops.',
            'is_published' => 1,
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'logo' => UploadedFile::fake()->image('logo.png', 256, 256),
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1600, 1000),
            'gallery' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ])->assertSessionHasNoErrors();

        $product = Product::firstOrFail();
        $this->assertSame('stocky', $product->slug);
        $this->assertSame('embed', $product->video()['type']);
        $this->assertCount(2, $product->images);
        Storage::disk('public')->assertExists([$product->logo, $product->cover_image]);

        // Edit: set alt text on one image, remove the other, add a third.
        [$first, $second] = $product->images;
        $this->actingAs($editor)->put("/admin/products/{$product->id}", [
            'name' => 'Stocky', 'status' => 'live', 'summary' => 'Inventory for small shops.', 'is_published' => 1,
            'gallery_alt' => [$first->id => 'Dashboard'],
            'remove_images' => [$second->id],
            'gallery' => [UploadedFile::fake()->image('c.jpg')],
        ])->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertCount(2, $product->images);
        $this->assertSame('Dashboard', $product->images->first()->alt);
        Storage::disk('public')->assertMissing($second->path);
        $this->assertSame('live', $product->status);

        // The public modal/page shows the lazily-loaded embed, not an iframe.
        $this->get('/products/stocky')->assertSee('data-embed-src="https://www.youtube-nocookie.com', false)->assertDontSee('<iframe', false);
    }

    public function test_non_youtube_video_links_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'X', 'status' => 'live', 'summary' => 'y', 'video_url' => 'https://example.com/clip.mp4',
        ])->assertSessionHasErrors('video_url');
    }

    public function test_deleting_a_product_removes_its_files_and_gallery(): void
    {
        Storage::fake('public');
        $product = $this->product(['cover_image' => UploadedFile::fake()->image('c.jpg')->store('products', 'public')]);
        $image = ProductImage::create(['product_id' => $product->id, 'path' => UploadedFile::fake()->image('g.jpg')->store('products/gallery', 'public')]);

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))->delete("/admin/products/{$product->id}");

        $this->assertModelMissing($product);
        $this->assertModelMissing($image);
        Storage::disk('public')->assertMissing([$product->cover_image, $image->path]);
    }

    public function test_sitemap_menu_and_admin_screens(): void
    {
        $this->product();
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->get('/sitemap.xml')->assertSee(route('products.show', 'attendly'), false);
        $this->get('/')->assertSee('href="'.route('products.index').'"', false);
        $this->actingAs($editor)->get('/admin/products')->assertOk()->assertSee('Attendly');
        $this->actingAs($editor)->get('/admin/products/create')->assertOk()->assertSee('YouTube or Vimeo link');
        $this->actingAs($editor)->get('/admin/products/'.Product::first()->id.'/edit')->assertOk()->assertSee('Key features');
    }

    public function test_main_menu_has_exactly_the_five_pages_and_blog_lives_in_the_footer(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        preg_match_all('#<span class="site-menu__label"[^>]*>([^<]+)</span>#', $html, $m);
        $this->assertSame(['Home', 'Services', 'Products', 'About', 'Contact'], $m[1]);

        preg_match('#<nav class="footer-links".*?</nav>#s', $html, $footer);
        $this->assertStringContainsString('href="'.route('blog.index').'"', $footer[0]);
        $this->assertStringContainsString('href="'.route('products.index').'"', $footer[0]);

        // Projects is gone entirely.
        $this->get('/projects')->assertNotFound();
        $this->assertStringNotContainsString('/projects', $html);
    }

    public function test_product_seeder_creates_solaros_once_with_its_media(): void
    {
        Storage::fake('public');

        $this->seed(\Database\Seeders\ProductSeeder::class);
        $this->seed(\Database\Seeders\ProductSeeder::class); // re-run is a no-op

        $this->assertSame(1, Product::count());
        $solar = Product::with('images')->firstOrFail();
        $this->assertTrue($solar->is_published && $solar->is_featured);
        $this->assertSame('https://solaros.voidlayer.in', $solar->product_url);
        $this->assertCount(5, $solar->images);
        Storage::disk('public')->assertExists([$solar->logo, $solar->cover_image, ...$solar->images->pluck('path')->all()]);

        $this->get('/')->assertSee('id="products"', false)->assertSee('SolarOS');
        $this->get('/products/solaros')->assertOk()->assertSee('From first enquiry to long-term trust');
    }

    public function test_an_oversized_upload_returns_to_the_form_with_a_message(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $form = route('admin.products.create');

        // call() ignores withHeaders(), so the referer goes in as a server var.
        $response = $this->actingAs($admin)
            ->call('POST', '/admin/products', [], [], [], ['CONTENT_LENGTH' => 1024 ** 4, 'HTTP_REFERER' => $form]);

        $response->assertRedirect($form.'?upload_too_large=1');
        $this->actingAs($admin)->get($form.'?upload_too_large=1')->assertSee('larger than this server accepts');
    }
}
