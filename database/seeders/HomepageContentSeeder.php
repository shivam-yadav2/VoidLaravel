<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FeatureCard;
use App\Models\Stat;
use App\Models\TechBadge;
use Illuminate\Database\Seeder;

class HomepageContentSeeder extends Seeder
{
    /**
     * Moves the content that used to be hardcoded in the Blade sections into
     * the database, so switching those sections over to the new CMS tables
     * doesn't blank the homepage out. Safe to re-run (firstOrCreate).
     *
     * Testimonials are deliberately NOT seeded: they must be real client
     * quotes, added at /admin/testimonials. The homepage section stays
     * hidden until at least one is active.
     */
    public function run(): void
    {
        $this->seedFaqs();
        $this->seedTechBadges();
        $this->seedStats();
        $this->seedFeatureCards();
    }

    private function seedFaqs(): void
    {
        $rows = [
            ['question' => 'What services does your agency offer?', 'answer' => 'We build custom software, ERP & HRMS systems, web applications and AI-powered automation end-to-end — from UI/UX design and frontend development to backend architecture, APIs, and cloud deployment.'],
            ['question' => 'How long does a typical project take?', 'answer' => 'Timelines depend on scope, but most projects range from 3 to 12 weeks. We share a clear roadmap with milestones during the discovery phase so you always know what to expect.'],
            ['question' => 'Can you work with our existing tech stack?', 'answer' => "Yes. Our team is experienced across modern stacks and can integrate with your existing codebase, tools, and infrastructure, or recommend a new stack if you're starting fresh."],
            ['question' => 'Do you offer post-launch support and maintenance?', 'answer' => 'Absolutely. We offer flexible support and maintenance plans after launch, including bug fixes, performance monitoring, and ongoing feature development.'],
            ['question' => 'How do we get started with a project?', 'answer' => "Just reach out through our contact form or book a call. We'll discuss your goals, scope the project together, and provide a proposal with timeline and pricing."],
        ];

        foreach ($rows as $i => $row) {
            Faq::firstOrCreate(['question' => $row['question']], [...$row, 'is_active' => true, 'sort_order' => $i]);
        }
    }

    private function seedTechBadges(): void
    {
        $rows = [
            ['title' => 'React & TypeScript', 'subtitle' => 'Modern Frontend'],
            ['title' => 'Node.js & PostgreSQL', 'subtitle' => 'Robust Backend'],
            ['title' => 'AWS & Azure', 'subtitle' => 'Cloud Hosting'],
            ['title' => 'Razorpay & Stripe', 'subtitle' => 'Payment Gateways'],
            ['title' => 'ERP & HRMS', 'subtitle' => 'System Integration'],
            ['title' => 'REST & GraphQL', 'subtitle' => 'API Integration'],
            ['title' => 'AI & LLM APIs', 'subtitle' => 'Smart Automation'],
            ['title' => 'CI/CD Pipelines', 'subtitle' => 'DevOps Delivery'],
        ];

        foreach ($rows as $i => $row) {
            TechBadge::firstOrCreate(['title' => $row['title']], [...$row, 'is_active' => true, 'sort_order' => $i]);
        }
    }

    private function seedStats(): void
    {
        $rows = [
            ['value' => '50+', 'label' => 'Products shipped'],
            ['value' => '12', 'label' => 'Countries served'],
            ['value' => '1 day', 'label' => 'Reply guarantee'],
            ['value' => '100%', 'label' => 'Code you own'],
        ];

        foreach ($rows as $i => $row) {
            Stat::firstOrCreate(['value' => $row['value'], 'label' => $row['label']], [...$row, 'is_active' => true, 'sort_order' => $i]);
        }
    }

    private function seedFeatureCards(): void
    {
        $services = [
            ['title' => 'Custom Software Development', 'description' => 'Business applications built around how your team actually works — from internal tools to customer-facing systems, scoped to your workflows and data.'],
            ['title' => 'ERP & HRMS Solutions', 'description' => 'Enterprise resource planning and HR management systems that bring your operations, staff and data into one reliable place.'],
            ['title' => 'Web Application Development', 'description' => 'Fast, accessible web apps built on modern React and TypeScript, with the performance and polish your users actually notice.'],
            ['title' => 'Business Process Automation', 'description' => 'We map out the manual, repetitive parts of your workflow and replace them with automation — less busywork, fewer errors, more time for your team.'],
            ['title' => 'AI-Powered & SaaS Solutions', 'description' => 'AI copilots, workflow automation and multi-tenant SaaS products — wired safely into your product and your data.'],
        ];

        $aboutValues = [
            ['title' => 'Senior by default', 'description' => 'No junior teams learning on your budget. The people who scope your project are the people who build it — end to end.'],
            ['title' => 'Ship, then iterate', 'description' => 'We get real software in front of real users early, then improve it with evidence — not opinions or endless planning decks.'],
            ['title' => 'Own the outcome', 'description' => "We measure ourselves on your product working in production — not on hours billed. If it's live and healthy, we've done our job."],
        ];

        foreach ($services as $i => $row) {
            FeatureCard::firstOrCreate(
                ['group' => FeatureCard::GROUP_SERVICES, 'title' => $row['title']],
                [...$row, 'group' => FeatureCard::GROUP_SERVICES, 'is_active' => true, 'sort_order' => $i]
            );
        }

        foreach ($aboutValues as $i => $row) {
            FeatureCard::firstOrCreate(
                ['group' => FeatureCard::GROUP_ABOUT_VALUES, 'title' => $row['title']],
                [...$row, 'group' => FeatureCard::GROUP_ABOUT_VALUES, 'is_active' => true, 'sort_order' => $i]
            );
        }
    }
}
