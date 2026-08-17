<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\FeatureCard;
use App\Models\Stat;
use App\Models\TechBadge;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_testimonial_shows_on_homepage_and_inactive_does_not(): void
    {
        Testimonial::create(['client_name' => 'Ada Lovelace', 'quote' => 'Great team.', 'rating' => 5, 'is_active' => true]);
        Testimonial::create(['client_name' => 'Hidden Client', 'quote' => 'Should not show.', 'rating' => 5, 'is_active' => false]);

        $response = $this->get('/');

        $response->assertSee('Ada Lovelace')->assertDontSee('Hidden Client');
    }

    public function test_faqs_render_in_sort_order(): void
    {
        Faq::create(['question' => 'Second question', 'answer' => 'x', 'sort_order' => 2]);
        Faq::create(['question' => 'First question', 'answer' => 'x', 'sort_order' => 1]);

        $html = $this->get('/')->getContent();

        $this->assertTrue(strpos($html, 'First question') < strpos($html, 'Second question'));
    }

    public function test_admin_can_manage_testimonials(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/testimonials', [
            'client_name' => 'New Client',
            'quote' => 'Solid work.',
            'rating' => 4,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('testimonials', ['client_name' => 'New Client']);

        $testimonial = Testimonial::firstWhere('client_name', 'New Client');

        $this->actingAs($admin)->delete("/admin/testimonials/{$testimonial->id}")->assertRedirect();
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }

    public function test_feature_card_group_scopes_correctly(): void
    {
        FeatureCard::create(['group' => 'services', 'title' => 'A service', 'is_active' => true]);
        FeatureCard::create(['group' => 'about_values', 'title' => 'A value', 'is_active' => true]);

        $this->get('/services')->assertSee('A service')->assertDontSee('A value');
        $this->get('/about')->assertSee('A value')->assertDontSee('A service');
    }

    public function test_guests_cannot_manage_homepage_content(): void
    {
        foreach (['/admin/testimonials', '/admin/faqs', '/admin/feature-cards', '/admin/tech-badges', '/admin/stats'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
    }

    public function test_sections_render_nothing_when_empty_rather_than_erroring(): void
    {
        // No testimonials, faqs, badges, stats, or cards seeded at all.
        foreach (['/', '/services', '/about'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_stat_value_is_not_rendered_with_counterup_class(): void
    {
        Stat::create(['value' => '1 day', 'label' => 'Reply guarantee', 'is_active' => true]);

        // main.js runs $(".count").counterUp() globally and expects a pure
        // number; a free-text stat value must never get that class.
        $html = $this->get('/about')->getContent();

        $this->assertStringNotContainsString('<span class="count">1 day</span>', $html);
        $this->assertStringContainsString('1 day', $html);
    }

    public function test_tech_badges_render_on_homepage(): void
    {
        TechBadge::create(['title' => 'Laravel & MySQL', 'subtitle' => 'Backend', 'is_active' => true]);

        $this->get('/')->assertSee('Laravel & MySQL')->assertSee('Backend');
    }
}
