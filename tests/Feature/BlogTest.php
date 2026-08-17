<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_published_post_is_visible_on_the_blog(): void
    {
        $post = Post::create([
            'title' => 'Shipping an ERP',
            'slug' => 'shipping-an-erp',
            'excerpt' => 'How we do it.',
            'content' => '<p>Body copy.</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog')->assertOk()->assertSee('Shipping an ERP');
        $this->get("/blog/{$post->slug}")->assertOk()->assertSee('Body copy.', false);
    }

    public function test_draft_post_is_hidden_from_the_public_site(): void
    {
        $post = Post::create([
            'title' => 'Secret draft',
            'slug' => 'secret-draft',
            'content' => '<p>Not ready.</p>',
            'status' => Post::STATUS_DRAFT,
        ]);

        $this->get('/blog')->assertOk()->assertDontSee('Secret draft');
        $this->get("/blog/{$post->slug}")->assertNotFound();
    }

    public function test_scheduled_post_is_hidden_until_its_publish_date(): void
    {
        $post = Post::create([
            'title' => 'Future post',
            'slug' => 'future-post',
            'content' => '<p>Later.</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->addWeek(),
        ]);

        $this->get("/blog/{$post->slug}")->assertNotFound();
        $this->get('/blog')->assertDontSee('Future post');
    }

    public function test_admin_can_create_a_post_with_a_featured_image(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Engineering', 'slug' => 'engineering']);

        $response = $this->actingAs($this->admin())->post('/admin/posts', [
            'title' => 'Uploading works',
            'slug' => '',
            'excerpt' => 'Testing the upload path.',
            'content' => '<p>Content.</p>',
            'category_id' => $category->id,
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => '',
            'featured_image' => UploadedFile::fake()->image('hero.jpg', 1200, 630),
            'featured_image_alt' => 'A hero image',
        ]);

        $response->assertRedirect();

        $post = Post::firstWhere('slug', 'uploading-works');
        $this->assertNotNull($post, 'Slug should be generated from the title.');
        $this->assertNotNull($post->featured_image);
        Storage::disk('public')->assertExists($post->featured_image);
        $this->assertNotNull($post->published_at, 'Publishing without a date should default to now.');
    }

    public function test_admin_can_replace_and_remove_the_featured_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/posts', [
            'title' => 'Image swap',
            'content' => '<p>x</p>',
            'status' => Post::STATUS_DRAFT,
            'featured_image' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $post = Post::firstWhere('slug', 'image-swap');
        $original = $post->featured_image;

        // Replace
        $this->actingAs($admin)->put("/admin/posts/{$post->id}", [
            'title' => 'Image swap',
            'slug' => 'image-swap',
            'content' => '<p>x</p>',
            'status' => Post::STATUS_DRAFT,
            'featured_image' => UploadedFile::fake()->image('second.jpg'),
        ]);

        $post->refresh();
        $this->assertNotSame($original, $post->featured_image);
        Storage::disk('public')->assertMissing($original);

        // Remove
        $this->actingAs($admin)->put("/admin/posts/{$post->id}", [
            'title' => 'Image swap',
            'slug' => 'image-swap',
            'content' => '<p>x</p>',
            'status' => Post::STATUS_DRAFT,
            'remove_image' => '1',
        ]);

        $post->refresh();
        $this->assertNull($post->featured_image);
    }

    public function test_guests_cannot_reach_the_blog_admin(): void
    {
        $this->get('/admin/posts')->assertRedirect('/admin/login');
        $this->post('/admin/posts', [])->assertRedirect('/admin/login');
    }

    public function test_category_page_lists_only_its_posts(): void
    {
        $engineering = Category::create(['name' => 'Engineering', 'slug' => 'engineering']);
        $product = Category::create(['name' => 'Product', 'slug' => 'product']);

        Post::create([
            'title' => 'An engineering post', 'slug' => 'an-engineering-post',
            'content' => '<p>x</p>', 'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(), 'category_id' => $engineering->id,
        ]);
        Post::create([
            'title' => 'A product post', 'slug' => 'a-product-post',
            'content' => '<p>x</p>', 'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(), 'category_id' => $product->id,
        ]);

        $this->get('/blog/category/engineering')
            ->assertOk()
            ->assertSee('An engineering post')
            ->assertDontSee('A product post');
    }

    public function test_post_detail_emits_article_structured_data(): void
    {
        Post::create([
            'title' => 'Structured data post', 'slug' => 'structured-data-post',
            'excerpt' => 'A summary.', 'content' => '<p>x</p>',
            'status' => Post::STATUS_PUBLISHED, 'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/structured-data-post')
            ->assertOk()
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('<meta property="og:type" content="article" />', false)
            ->assertSee('A summary.', false);
    }
}
