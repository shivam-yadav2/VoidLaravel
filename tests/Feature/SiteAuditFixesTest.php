<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\LeadController;
use App\Mail\NewLeadReceived;
use App\Models\Category;
use App\Models\Lead;
use App\Models\Media;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Regression coverage for the fixes from the October 2026 site audit.
 */
class SiteAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function livePost(string $slug, array $extra = []): Post
    {
        return Post::create([
            'title' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'content' => '<p>Body.</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            ...$extra,
        ]);
    }

    // --- Redirects -------------------------------------------------------

    public function test_redirects_work_for_multi_segment_and_file_style_paths(): void
    {
        Redirect::create(['from_path' => '/old/section/page', 'to_path' => '/services', 'status_code' => 301, 'is_active' => true]);
        Redirect::create(['from_path' => '/about_us.html', 'to_path' => '/about', 'status_code' => 301, 'is_active' => true]);

        $this->get('/old/section/page')->assertStatus(301)->assertRedirect('/services');
        $this->get('/about_us.html')->assertStatus(301)->assertRedirect('/about');
    }

    public function test_renaming_a_live_post_slug_redirects_the_old_url(): void
    {
        $post = $this->livePost('first-name');

        $this->actingAs($this->admin())->put("/admin/posts/{$post->id}", [
            'title' => 'Renamed', 'slug' => 'second-name', 'content' => '<p>Body.</p>', 'status' => Post::STATUS_PUBLISHED,
            'published_at' => $post->published_at->displayTz()->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors();

        $this->get('/blog/first-name')->assertStatus(301)->assertRedirect('/blog/second-name');
        $this->get('/blog/second-name')->assertOk();
    }

    public function test_renaming_a_post_back_does_not_create_a_redirect_loop(): void
    {
        $post = $this->livePost('alpha');
        $admin = $this->admin();
        $save = fn (string $slug) => $this->actingAs($admin)->put("/admin/posts/{$post->id}", [
            'title' => 'T', 'slug' => $slug, 'content' => '<p>x</p>', 'status' => Post::STATUS_PUBLISHED,
            'published_at' => $post->fresh()->published_at->displayTz()->format('Y-m-d\TH:i'),
        ]);

        $save('beta');
        $save('gamma');
        $save('alpha');

        $this->get('/blog/alpha')->assertOk();
        $this->get('/blog/beta')->assertRedirect('/blog/alpha');
        $this->get('/blog/gamma')->assertRedirect('/blog/alpha');
    }

    public function test_a_redirect_cannot_point_at_itself_or_at_a_script_url(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/redirects', ['from_path' => '/loop', 'to_path' => '/loop/', 'status_code' => 301, 'is_active' => 1])
            ->assertSessionHasErrors('to_path');
        $this->actingAs($admin)->post('/admin/redirects', ['from_path' => '/x', 'to_path' => 'javascript:alert(1)', 'status_code' => 301, 'is_active' => 1])
            ->assertSessionHasErrors('to_path');

        $this->assertSame(0, Redirect::count());
    }

    // --- Leads -----------------------------------------------------------

    public function test_csv_export_neutralises_formulas_and_starts_with_a_utf8_bom(): void
    {
        Lead::create(['name' => '=HYPERLINK("http://evil","x")', 'email' => 'a@b.co', 'brief' => '+cmd', 'budget' => Lead::BUDGETS[1], 'status' => Lead::STATUS_NEW]);

        $csv = $this->actingAs($this->admin())->get('/admin/leads/export')->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+cmd", $csv);
        $this->assertStringContainsString('₹2 – 5 lakh', $csv);
    }

    public function test_csv_safe_leaves_ordinary_values_alone(): void
    {
        $this->assertSame('Acme Ltd', LeadController::csvSafe('Acme Ltd'));
        $this->assertNull(LeadController::csvSafe(null));
        $this->assertSame("'-5", LeadController::csvSafe('-5'));
    }

    public function test_new_lead_emails_the_team_with_reply_to_the_visitor(): void
    {
        Mail::fake();
        Setting::setMany(['lead_notification_email' => 'sales@voidlayer.in']);

        $this->post('/contact', [
            'name' => 'Asha', 'email' => 'asha@example.com', 'brief' => 'An HRMS for 200 staff.', 'budget' => 'Not sure yet',
        ])->assertRedirect(route('contact').'#contact-form');

        Mail::assertSent(NewLeadReceived::class, fn ($mail) => $mail->hasTo('sales@voidlayer.in') && $mail->hasReplyTo('asha@example.com'));
    }

    public function test_a_mail_failure_never_loses_the_lead(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->post('/contact', ['name' => 'Ravi', 'email' => 'ravi@example.com', 'brief' => 'Need an ERP.'])
            ->assertSessionHas('contact_success');

        $this->assertDatabaseHas('leads', ['email' => 'ravi@example.com']);
    }

    public function test_expired_contact_form_returns_the_visitor_with_their_message(): void
    {
        $request = Request::create('/contact', 'POST', ['name' => 'Meera', 'email' => 'm@example.com', 'brief' => 'Long message', '_token' => 'stale']);
        $request->setLaravelSession($this->app['session.store']);
        $request->setRouteResolver(fn () => $this->app['router']->getRoutes()->match($request));
        $this->app->instance('request', $request);

        $response = $this->app->make(ExceptionHandler::class)->render($request, new HttpException(419));

        $this->assertSame(route('contact').'#contact-form', $response->headers->get('Location'));
        $this->assertSame('Long message', $this->app['session.store']->get('_old_input.brief'));
        $this->assertNull($this->app['session.store']->get('_old_input._token'));
        $this->assertNotNull($this->app['session.store']->get('contact_error'));
    }

    // --- Blog SEO edge cases -------------------------------------------

    public function test_blog_page_past_the_end_is_a_404_not_an_empty_page(): void
    {
        $this->livePost('only-post');

        $this->get('/blog?page=1')->assertOk();
        $this->get('/blog?page=7')->assertNotFound();
    }

    public function test_blog_search_results_and_empty_categories_are_noindex(): void
    {
        $empty = Category::create(['name' => 'Empty', 'slug' => 'empty']);

        $this->get('/blog?q=erp')->assertSee('content="noindex, follow"', false);
        $this->get('/blog/category/empty')->assertOk()->assertSee('content="noindex, follow"', false);
        $this->get('/blog')->assertSee('index, follow, max-image-preview', false);
    }

    public function test_blog_search_treats_like_wildcards_literally(): void
    {
        $this->livePost('one', ['title' => 'Plain title']);

        $this->get('/blog?q=%25')->assertDontSee('Plain title');
        $this->get('/blog?q=Plain')->assertSee('Plain title');
    }

    public function test_sitemap_skips_categories_that_only_have_drafts(): void
    {
        $drafts = Category::create(['name' => 'Drafty', 'slug' => 'drafty']);
        Post::create(['title' => 'D', 'slug' => 'd', 'content' => 'x', 'status' => Post::STATUS_DRAFT, 'category_id' => $drafts->id]);

        $this->get('/sitemap.xml')->assertDontSee('/blog/category/drafty');
    }

    public function test_publish_date_is_entered_in_display_timezone_and_stored_in_utc(): void
    {
        $this->actingAs($this->admin())->post('/admin/posts', [
            'title' => 'Scheduled', 'content' => '<p>x</p>', 'status' => Post::STATUS_PUBLISHED,
            'published_at' => '2030-01-01T10:00', // 10:00 IST
        ]);

        $this->assertSame('2030-01-01 04:30:00', Post::first()->published_at->utc()->format('Y-m-d H:i:s'));
    }

    // --- Security --------------------------------------------------------

    public function test_a_totp_code_cannot_be_replayed(): void
    {
        $user = User::factory()->create(['email' => 'otp@example.com', 'password' => bcrypt('password123')]);
        $secret = Totp::generateSecret();
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now()])->save();
        $code = Totp::code($secret);

        $this->post('/admin/login', ['email' => 'otp@example.com', 'password' => 'password123']);
        $this->post('/admin/2fa', ['code' => $code])->assertRedirect(route('admin.dashboard'));
        $this->post('/admin/logout');

        $this->post('/admin/login', ['email' => 'otp@example.com', 'password' => 'password123']);
        $this->post('/admin/2fa', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_csp_allows_google_fonts_ga4_and_admin_listed_domains(): void
    {
        Setting::setMany(['csp_extra_domains' => "https://embed.tawk.to\nnot a domain; script-src *"]);

        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://fonts.googleapis.com', $csp);
        $this->assertStringContainsString('https://fonts.gstatic.com', $csp);
        $this->assertStringContainsString('https://*.google-analytics.com', $csp);
        $this->assertStringContainsString('https://embed.tawk.to', $csp);
        $this->assertStringNotContainsString('script-src *', $csp);
    }

    public function test_trackers_wait_for_consent(): void
    {
        $this->get('/')->assertDontSee('consent-banner', false);

        Setting::setMany(['google_analytics_id' => 'G-TESTID123']);

        $this->get('/')
            ->assertSee('id="consent-banner"', false)
            ->assertSee('window.vlLoadTrackers', false)
            ->assertDontSee('<script async src="https://www.googletagmanager.com', false);
    }

    public function test_a_malformed_analytics_id_is_never_written_into_javascript(): void
    {
        Setting::setMany(['google_analytics_id' => "G-X');alert(1);//"]);

        $this->get('/')->assertDontSee('alert(1)', false);
    }

    public function test_media_still_used_in_a_post_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $media = Media::create(['path' => 'media/diagram.png', 'original_name' => 'diagram.png', 'mime_type' => 'image/png', 'size' => 10, 'uploaded_by' => $admin->id]);
        $this->livePost('uses-image', ['content' => '<img src="/storage/media/diagram.png">']);

        $this->actingAs($admin)->delete("/admin/media/{$media->id}")->assertSessionHas('admin_error');

        $this->assertModelExists($media);
    }

    // --- Content ---------------------------------------------------------

    public function test_homepage_has_no_theme_placeholder_copy(): void
    {
        $this->seed(\Database\Seeders\HomepageContentSeeder::class);

        $html = $this->get('/')->assertOk()->getContent();

        foreach (['Figma Files Included', 'One Click Install', 'Buy Once', 'EXPERT TRAINERS', '1920 x 1080', 'Grate template', 'Theresa Webb'] as $junk) {
            $this->assertStringNotContainsString($junk, $html, "Placeholder copy still on the homepage: {$junk}");
        }

        $this->assertStringNotContainsString('alt="img"', $html);
    }

    public function test_contact_form_links_to_the_real_privacy_policy(): void
    {
        $this->get('/contact')
            ->assertSee('href="'.route('page.show', 'privacy-policy').'"', false)
            ->assertDontSee('href="#privacy"', false);
    }

    public function test_unknown_urls_get_the_branded_404_page(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('Page not found')->assertSee('content="noindex, follow"', false);
    }

    public function test_404_page_text_comes_from_settings_and_links_follow_the_menu(): void
    {
        Setting::setMany(['not_found_title' => 'Lost in the void', 'not_found_message' => 'Nothing lives at this address.'], 'not_found');

        $html = $this->get('/definitely-missing')->assertNotFound()
            ->assertSee('Lost in the void')
            ->assertSee('Nothing lives at this address.')
            ->getContent();

        preg_match('#<nav class="error-hero__links.*?</nav>#s', $html, $m);
        foreach (['services', 'products.index', 'about', 'contact'] as $route) {
            $this->assertStringContainsString('href="'.route($route).'"', $m[0]);
        }
    }

    public function test_homepage_title_leads_with_the_brand_not_home(): void
    {
        $this->get('/')->assertSee('<title>Void Layer — ', false)->assertDontSee('<title>Home |', false);
    }

    public function test_database_seeder_creates_no_login_accounts(): void
    {
        $this->seed();

        $this->assertSame(0, User::count());
    }
}
