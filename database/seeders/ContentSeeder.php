<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Seeds the settings rows and the two locked policy pages so the site has
     * something sensible to render before an admin edits anything. Safe to
     * re-run: existing rows keep their edited values.
     */
    public function run(): void
    {
        $this->seedSettings();
        $this->seedPages();
        $this->seedCategories();
    }

    private function seedSettings(): void
    {
        foreach (config('site.defaults') as $key => $value) {
            // firstOrCreate, not updateOrCreate: never clobber an admin's edit.
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => 'general']);
        }

        Setting::flush();
        $this->command?->info('Settings seeded.');
    }

    private function seedPages(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'meta_description' => 'How Void Layer collects, uses and protects the personal information you share with us.',
                'content' => $this->privacyContent(),
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'meta_description' => 'The terms that govern your use of the Void Layer website and services.',
                'content' => $this->termsContent(),
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(
                ['slug' => $page['slug']],
                [...$page, 'is_published' => true, 'is_locked' => true]
            );
        }

        $this->command?->info('Policy pages seeded.');
    }

    private function seedCategories(): void
    {
        foreach ([
            ['name' => 'Engineering', 'description' => 'How we build, test and ship software.'],
            ['name' => 'Product', 'description' => 'Scoping, design decisions and shipping the right thing.'],
            ['name' => 'Automation', 'description' => 'ERP, HRMS and taking manual work out of the day.'],
        ] as $category) {
            Category::firstOrCreate(
                ['slug' => str($category['name'])->slug()->toString()],
                $category
            );
        }

        $this->command?->info('Blog categories seeded.');
    }

    private function privacyContent(): string
    {
        return <<<'HTML'
<p>This policy explains what information Void Layer collects when you use our website, why we collect it, and what we do with it. We keep this deliberately short and readable.</p>

<h2>Information we collect</h2>
<p>We only collect what you actively give us. When you submit the contact form, we store the name, email address, company, budget range and project brief you enter, along with your IP address and the time of submission.</p>
<p>We do not run advertising trackers or sell data to third parties.</p>

<h2>How we use it</h2>
<ul>
  <li>To reply to your enquiry and discuss your project.</li>
  <li>To keep a record of the conversation so we can pick it up later.</li>
  <li>To improve how we scope and estimate work.</li>
</ul>

<h2>How long we keep it</h2>
<p>Enquiries are kept for as long as there is an active or plausible working relationship. You can ask us to delete your data at any time and we will do so promptly.</p>

<h2>Sharing</h2>
<p>We do not share your information with third parties except where required to operate our business (for example, our email provider) or where legally required.</p>

<h2>Cookies</h2>
<p>The site always sets two essential cookies: a session cookie and a security token that protects forms (CSRF protection). Neither is used for tracking or profiling.</p>
<p>If we enable analytics (such as Google Analytics or the Meta Pixel), those tools set their own cookies <strong>only after you click &ldquo;Accept&rdquo;</strong> on the cookie banner. If you decline, they are never loaded. You can reset your choice at any time by clearing this site&rsquo;s data in your browser.</p>

<h2>Your rights</h2>
<p>You can ask us what data we hold about you, ask for corrections, or ask us to delete it. Email us and we will respond within a reasonable period.</p>

<h2>Changes to this policy</h2>
<p>If this policy changes materially, we will update the date shown at the top of this page.</p>

<h2>Contact</h2>
<p>Questions about this policy? Get in touch and we will answer directly.</p>
HTML;
    }

    private function termsContent(): string
    {
        return <<<'HTML'
<p>These terms govern your use of the Void Layer website. By using the site you agree to them. Work we deliver for clients is governed separately by the signed agreement for that engagement.</p>

<h2>Use of this website</h2>
<p>You may browse and use this site for lawful purposes. You agree not to attempt to disrupt the site, gain unauthorised access to it, or use it to transmit unlawful or malicious content.</p>

<h2>Intellectual property</h2>
<p>The content, design and code of this website are owned by Void Layer unless stated otherwise. You may not reproduce substantial parts of it without permission.</p>
<p>Code we write for a client under a signed agreement belongs to that client on the terms set out in the agreement.</p>

<h2>Enquiries and estimates</h2>
<p>Anything discussed before a signed agreement — including timelines, scope and indicative pricing — is an estimate offered in good faith, not a binding commitment. Scope and price are fixed only in a written agreement.</p>

<h2>Third-party links</h2>
<p>This site may link to third-party websites. We are not responsible for their content or their practices.</p>

<h2>Limitation of liability</h2>
<p>This website is provided as is. To the extent permitted by law, Void Layer is not liable for any loss arising from your use of, or inability to use, this website.</p>

<h2>Governing law</h2>
<p>These terms are governed by the laws of India, and the courts of Lucknow, Uttar Pradesh have exclusive jurisdiction.</p>

<h2>Changes to these terms</h2>
<p>We may update these terms from time to time. The date shown at the top of this page reflects the most recent revision.</p>
HTML;
    }
}
