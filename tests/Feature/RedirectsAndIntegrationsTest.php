<?php

namespace Tests\Feature;

use App\Models\Redirect;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectsAndIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_redirect_sends_visitors_to_the_new_path(): void
    {
        Redirect::create(['from_path' => '/old-page', 'to_path' => '/contact', 'status_code' => 301, 'is_active' => true]);

        $response = $this->get('/old-page');

        $response->assertRedirect('/contact');
        $response->assertStatus(301);
    }

    public function test_inactive_redirect_does_not_fire(): void
    {
        Redirect::create(['from_path' => '/old-page', 'to_path' => '/contact', 'status_code' => 301, 'is_active' => false]);

        $this->get('/old-page')->assertNotFound();
    }

    public function test_unknown_slug_with_no_redirect_still_404s(): void
    {
        $this->get('/totally-made-up-page')->assertNotFound();
    }

    public function test_a_real_page_wins_over_any_matching_redirect(): void
    {
        \App\Models\Page::create(['slug' => 'about-us-old', 'title' => 'Kept', 'content' => 'x', 'is_published' => true]);
        Redirect::create(['from_path' => '/about-us-old', 'to_path' => '/about', 'status_code' => 301]);

        // A Page row always wins — PageController checks Page before Redirect.
        $this->get('/about-us-old')->assertOk()->assertSee('Kept');
    }

    public function test_redirect_hit_counter_increments(): void
    {
        $redirect = Redirect::create(['from_path' => '/old', 'to_path' => '/', 'status_code' => 302, 'is_active' => true]);

        $this->get('/old');
        $this->get('/old');

        $this->assertSame(2, $redirect->fresh()->hits);
    }

    public function test_admin_can_manage_redirects(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post('/admin/redirects', [
            'from_path' => 'old-slug', 'to_path' => '/new-slug', 'status_code' => 301, 'is_active' => '1',
        ])->assertRedirect();

        // Stored normalized: leading slash added even though submitted without one.
        $this->assertDatabaseHas('redirects', ['from_path' => '/old-slug', 'to_path' => '/new-slug']);
    }

    public function test_duplicate_redirect_path_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Redirect::create(['from_path' => '/dup', 'to_path' => '/a', 'status_code' => 301]);

        $this->actingAs($admin)->post('/admin/redirects', [
            'from_path' => '/dup', 'to_path' => '/b', 'status_code' => 301, 'is_active' => '1',
        ])->assertSessionHasErrors('from_path');
    }

    public function test_editor_cannot_manage_redirects(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->actingAs($editor)->get('/admin/redirects')->assertForbidden();
    }

    public function test_google_analytics_snippet_only_renders_when_configured(): void
    {
        $this->get('/')->assertDontSee('googletagmanager.com/gtag/js');

        Setting::setMany(['google_analytics_id' => 'G-TESTID123']);

        $this->get('/')->assertSee('googletagmanager.com/gtag/js?id=G-TESTID123', false);
    }

    public function test_custom_head_code_renders_verbatim_when_set(): void
    {
        Setting::setMany(['custom_head_code' => '<meta name="verification" content="abc123" />']);

        $this->get('/')->assertSee('<meta name="verification" content="abc123" />', false);
    }
}
