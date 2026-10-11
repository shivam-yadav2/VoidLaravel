<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Page;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    /* ---------------- Contact / leads ---------------- */

    public function test_contact_form_stores_a_lead(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@analytical.dev',
            'company' => 'Analytical Engines',
            'budget' => Lead::BUDGETS[1],
            'brief' => 'We need an ERP.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'email' => 'ada@analytical.dev',
            'budget' => Lead::BUDGETS[1],
            'status' => Lead::STATUS_NEW,
        ]);
    }

    public function test_contact_form_rejects_a_tampered_budget(): void
    {
        $this->post('/contact', [
            'name' => 'Bot', 'email' => 'b@x.io',
            'budget' => 'free lol', 'brief' => 'hi',
        ])->assertSessionHasErrors('budget');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_honeypot_blocks_spam_submissions(): void
    {
        $this->post('/contact', [
            'name' => 'Spam', 'email' => 'spam@bot.io',
            'brief' => 'buy things', 'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('leads', 0);
    }

    /* ---------------- Content pages ---------------- */

    public function test_published_page_renders_and_draft_does_not(): void
    {
        Page::create([
            'slug' => 'privacy-policy', 'title' => 'Privacy Policy',
            'content' => '<p>We respect your data.</p>', 'is_published' => true,
        ]);
        Page::create([
            'slug' => 'hidden-page', 'title' => 'Hidden',
            'content' => '<p>Nope.</p>', 'is_published' => false,
        ]);

        $this->get('/privacy-policy')->assertOk()->assertSee('We respect your data.', false);
        $this->get('/hidden-page')->assertNotFound();
    }

    public function test_page_slug_cannot_shadow_a_real_route(): void
    {
        // Even if a page claims the "services" slug, the real route still wins.
        Page::create([
            'slug' => 'services', 'title' => 'Hijack attempt',
            'content' => '<p>Should not render.</p>', 'is_published' => true,
        ]);

        $this->get('/services')->assertOk()->assertDontSee('Should not render.');
    }

    public function test_locked_page_keeps_its_slug_when_edited(): void
    {
        $page = Page::create([
            'slug' => 'privacy-policy', 'title' => 'Privacy Policy',
            'content' => '<p>Original.</p>', 'is_published' => true, 'is_locked' => true,
        ]);

        // A disabled slug input is not submitted by the browser at all.
        $this->actingAs(User::factory()->create())
            ->put("/admin/pages/{$page->id}", [
                'title' => 'Privacy Policy',
                'content' => '<p>Edited.</p>',
                'is_published' => '1',
            ])
            ->assertRedirect();

        $page->refresh();
        $this->assertSame('privacy-policy', $page->slug);
        $this->assertStringContainsString('Edited.', $page->content);
    }

    public function test_locked_page_cannot_be_deleted(): void
    {
        $page = Page::create([
            'slug' => 'terms-and-conditions', 'title' => 'Terms',
            'content' => '<p>x</p>', 'is_published' => true, 'is_locked' => true,
        ]);

        $this->actingAs(User::factory()->create())
            ->delete("/admin/pages/{$page->id}");

        $this->assertDatabaseHas('pages', ['id' => $page->id]);
    }

    /* ---------------- Settings ---------------- */

    public function test_settings_changes_appear_on_the_public_site(): void
    {
        Setting::setMany(['contact_email' => 'studio@example.com']);

        $this->get('/contact')->assertOk()->assertSee('studio@example.com');
    }

    public function test_settings_fall_back_to_config_defaults(): void
    {
        $this->assertSame(
            config('site.defaults.contact_email'),
            app(\App\Support\SiteConfig::class)->contact_email
        );
    }

    /* ---------------- SEO ---------------- */

    public function test_admin_meta_overrides_the_default_page_title(): void
    {
        SeoMeta::create([
            'key' => 'home',
            'meta_title' => 'Custom Home Title',
            'meta_description' => 'Custom home description.',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Custom Home Title | ', false)
            ->assertSee('Custom home description.', false);
    }

    public function test_noindex_flag_is_emitted_when_set(): void
    {
        SeoMeta::create(['key' => 'about', 'noindex' => true]);

        $this->get('/about')->assertSee('content="noindex, follow"', false);
        $this->get('/services')->assertSee('index, follow', false);
    }

    public function test_every_public_page_has_a_canonical_and_description(): void
    {
        foreach (['/', '/services', '/about', '/contact', '/blog'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('<link rel="canonical"', false)
                ->assertSee('<meta name="description"', false);
        }
    }

    public function test_sitemap_lists_published_content_only(): void
    {
        Page::create(['slug' => 'live-page', 'title' => 'Live', 'content' => 'x', 'is_published' => true]);
        Page::create(['slug' => 'draft-page', 'title' => 'Draft', 'content' => 'x', 'is_published' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('/live-page')
            ->assertDontSee('/draft-page');
    }

    /* ---------------- Admin access ---------------- */

    public function test_admin_area_requires_authentication(): void
    {
        foreach (['/admin/leads', '/admin/posts', '/admin/pages', '/admin/seo', '/admin/settings'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
    }

    public function test_admin_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['email' => 'admin@voidlayer.in']);

        $this->post('/admin/login', [
            'email' => 'admin@voidlayer.in',
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}
