<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaTest extends TestCase
{
    use RefreshDatabase;

    private function configureRecaptcha(): void
    {
        Setting::setMany([
            'recaptcha_site_key' => 'test-site-key',
            'recaptcha_secret_key' => 'test-secret-key',
        ]);
    }

    public function test_widget_does_not_render_when_not_configured(): void
    {
        $this->get('/contact')->assertDontSee('g-recaptcha');
    }

    public function test_widget_renders_once_keys_are_configured(): void
    {
        $this->configureRecaptcha();

        $this->get('/contact')
            ->assertSee('g-recaptcha')
            ->assertSee('data-sitekey="test-site-key"', false)
            ->assertSee('recaptcha/api.js', false);
    }

    public function test_submission_without_a_token_is_rejected_when_configured(): void
    {
        $this->configureRecaptcha();

        $this->post('/contact', [
            'name' => 'Ada', 'email' => 'ada@x.io', 'brief' => 'hello',
        ])->assertSessionHasErrors('g-recaptcha-response');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_submission_with_a_token_google_rejects_is_blocked(): void
    {
        $this->configureRecaptcha();
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false], 200)]);

        $this->post('/contact', [
            'name' => 'Ada', 'email' => 'ada@x.io', 'brief' => 'hello',
            'g-recaptcha-response' => 'fake-token',
        ])->assertSessionHasErrors('g-recaptcha-response');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_submission_with_a_token_google_accepts_creates_a_lead(): void
    {
        $this->configureRecaptcha();
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200)]);

        $this->post('/contact', [
            'name' => 'Ada', 'email' => 'ada@x.io', 'brief' => 'hello',
            'g-recaptcha-response' => 'fake-token',
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', ['email' => 'ada@x.io']);
    }

    public function test_contact_form_still_works_without_recaptcha_configured(): void
    {
        // Regression guard: reCAPTCHA being optional must not break the
        // plain contact flow that existed before this feature.
        $this->post('/contact', [
            'name' => 'Ada', 'email' => 'ada@x.io', 'brief' => 'hello',
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', ['email' => 'ada@x.io']);
    }
}
