<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Seeds Void Layer's own products into Admin → Products.
 *
 * Copy is taken from each product's live site; images are real captures
 * stored in database/seeders/assets/products/{slug}/ and copied to the
 * public disk. Safe to re-run: an existing product (matched by slug) is left
 * untouched, so edits made in the admin are never overwritten.
 *
 *   php artisan db:seed --class=ProductSeeder
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSolarOs();
    }

    private function seedSolarOs(): void
    {
        if (Product::where('slug', 'solaros')->exists()) {
            $this->command?->info('SolarOS already exists — left unchanged.');

            return;
        }

        $files = $this->copyAssets('solaros', [
            'logo.webp', 'cover.webp', 'features.webp', 'workflow.webp', 'request-demo.webp', 'faq.webp', 'product-art.webp',
        ]);

        $product = Product::create([
            'name' => 'SolarOS',
            'slug' => 'solaros',
            'tagline' => 'ERP, CRM & customer portal for solar EPC companies',
            'category' => 'SaaS',
            'status' => 'live',
            'summary' => 'Run your solar EPC business from lead to lifetime service: CRM, projects, procurement, billing, profitability, HR, customer portal and support — in one secure, multi-tenant workspace.',
            'description' => $this->solarOsDescription(),
            'features' => implode("\n", [
                'Lead-to-project CRM',
                'Solar project execution — capacity, BoQ, DISCOM stages and daily site progress',
                'Payments, expenses and customer-wise profitability',
                'Margin intelligence before margins drift',
                'Customer self-service portal on web and Android',
                'Roles, fine-grained permissions and audit trail',
                'Team, attendance and payroll',
                'After-sales service, warranties and tickets',
            ]),
            'tech_stack' => null, // not stated on the product site — add in admin if wanted
            'logo' => $files['logo.webp'],
            'cover_image' => $files['cover.webp'],
            'cover_image_alt' => 'SolarOS home page: “Run your solar business from lead to lifetime service.”',
            'product_url' => 'https://solaros.voidlayer.in',
            'cta_label' => 'Visit SolarOS',
            'is_published' => true,
            'is_featured' => true,
            'sort_order' => 0,
            'meta_title' => 'SolarOS — Solar EPC ERP, CRM & Customer Portal',
            'meta_description' => 'SolarOS by Void Layer: CRM, project execution, procurement, billing, profitability, payroll, customer portal and after-sales service for solar EPC companies.',
        ]);

        $gallery = [
            'features.webp' => 'SolarOS features: CRM, project execution, payments, margin intelligence, customer portal, roles, payroll and after-sales service',
            'workflow.webp' => 'SolarOS lifecycle: capture and qualify, plan and procure, execute and collect, serve and grow',
            'request-demo.webp' => 'SolarOS personalised demo request form',
            'faq.webp' => 'SolarOS frequently asked questions',
            'product-art.webp' => 'SolarOS 3D product artwork: a solar panel on a glass plinth',
        ];

        $order = 1;
        foreach ($gallery as $file => $alt) {
            ProductImage::create(['product_id' => $product->id, 'path' => $files[$file], 'alt' => $alt, 'sort_order' => $order++]);
        }

        $this->command?->info('SolarOS seeded with '.count($gallery).' gallery images.');
    }

    /**
     * @param  list<string>  $names
     * @return array<string, string> file name => public-disk path
     */
    private function copyAssets(string $slug, array $names): array
    {
        $paths = [];
        foreach ($names as $name) {
            $source = database_path("seeders/assets/products/{$slug}/{$name}");
            $target = "products/{$slug}/{$name}";
            Storage::disk('public')->put($target, file_get_contents($source));
            $paths[$name] = $target;
        }

        return $paths;
    }

    private function solarOsDescription(): string
    {
        return <<<'HTML'
<p>SolarOS brings CRM, project execution, procurement, payments, customer-wise profitability, payroll and after-sales service into one secure operating system — built for modern solar EPC operations.</p>

<ul>
  <li><strong>One workspace</strong> — sales, sites and service</li>
  <li><strong>Customer-wise</strong> — finance and margin clarity</li>
  <li><strong>Multi-tenant</strong> — secure workspace isolation</li>
  <li><strong>Web + Android</strong> — office and field access</li>
</ul>

<h3>Every team works from the same truth</h3>
<p>Replace disconnected spreadsheets and chat threads with workflows designed around how solar EPC companies actually sell, execute and support installations.</p>
<ul>
  <li><strong>Lead-to-project CRM</strong> — capture prospects, follow-ups, quotations, surveys and conversions without losing context between teams.</li>
  <li><strong>Solar project execution</strong> — plan capacity, hardware, BoQ, DISCOM stages, attendance and daily site progress in one operational timeline.</li>
  <li><strong>Payments and expenses</strong> — track milestone collections, customer-wise site costs, invoices and actual profitability with reconciled numbers.</li>
  <li><strong>Margin intelligence</strong> — see customer-wise contract value, expenses, realized profit and project contribution before margins drift.</li>
  <li><strong>Customer self-service</strong> — give every homeowner a secure portal for project progress, invoices, receipts, documents and service tickets.</li>
  <li><strong>Roles and audit trail</strong> — control staff access with fine-grained permissions and retain an immutable activity history across the workspace.</li>
  <li><strong>Team and payroll</strong> — manage employees, payroll runs, salary advances, site attendance and operational accountability.</li>
  <li><strong>After-sales service</strong> — keep warranty cards, complaints, documents and customer conversations connected long after commissioning.</li>
</ul>

<h3>From first enquiry to long-term trust</h3>
<p>Every handoff keeps the customer, project, commercial and service context attached — so your teams move faster without losing accountability.</p>
<ol>
  <li><strong>Capture &amp; qualify</strong> — website enquiry, CRM lead, follow-ups and proposal.</li>
  <li><strong>Plan &amp; procure</strong> — system design, project creation, BoQ and inventory.</li>
  <li><strong>Execute &amp; collect</strong> — DPR, DISCOM, milestones, payments and site expenses.</li>
  <li><strong>Serve &amp; grow</strong> — portal, warranty, tickets, documents and referrals.</li>
</ol>

<h3>Questions, answered</h3>
<p><strong>Is SolarOS built only for solar EPC companies?</strong><br>Yes. The workflows, terminology and reports are designed around rooftop solar sales, execution, billing, DISCOM coordination and lifetime customer service.</p>
<p><strong>Can multiple companies use the same platform securely?</strong><br>Yes. SolarOS is multi-tenant by design. Every EPC workspace has isolated records, branding, users, permissions and subscription limits.</p>
<p><strong>Does it include a customer mobile experience?</strong><br>Yes. Customers can securely access project status, payments, invoices, warranties, documents and service requests from web and Android experiences.</p>
<p><strong>Can we start with our existing customer and project data?</strong><br>Yes. During onboarding we can map your current spreadsheets and operating process into the SolarOS customer, project and finance structure.</p>
<p><strong>Are plans and user limits configurable?</strong><br>Yes. Subscription packages control projects, users and enabled modules. Contact us if your operation needs a custom enterprise package.</p>
HTML;
    }
}
