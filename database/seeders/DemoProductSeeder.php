<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Fictional DEMO products for previewing the products grid locally. They
 * are not real Void Layer products — never run this on production. Kept
 * out of DatabaseSeeder on purpose.
 *
 *   php artisan db:seed --class=DemoProductSeeder
 *
 * Remove them again (slugs all start with "demo-"):
 *
 *   php artisan tinker --execute "App\Models\Product::where('slug', 'like', 'demo-%')->delete();"
 *
 * Images are generated mock-ups in database/seeders/assets/products/demo-*.
 */
class DemoProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'LedgerLoop',
                'tagline' => 'GST invoicing that chases payments for you',
                'category' => 'SaaS',
                'status' => 'live',
                'summary' => 'Create GST-ready invoices, send payment links on WhatsApp and email, and let automatic reminders follow up on overdue bills.',
                'features' => ['GST invoices — HSN codes, tax splits and e-invoice export', 'Payment links — UPI, cards and net banking', 'Auto reminders — polite nudges before and after due dates', 'Cash-flow reports — collected vs. outstanding at a glance'],
                'tech_stack' => 'Laravel, Vue, MySQL, Razorpay',
                'product_url' => 'https://example.com/ledgerloop',
            ],
            [
                'name' => 'ShiftDesk',
                'tagline' => 'Staff rosters and attendance in one app',
                'category' => 'Mobile app',
                'status' => 'beta',
                'summary' => 'Build weekly rosters with drag and drop, let staff swap shifts from their phones, and track attendance with geo-fenced check-ins.',
                'features' => ['Drag-and-drop rosters — copy last week in one click', 'Shift swaps — approved by a manager in the app', 'Geo-fenced check-in — attendance only on site', 'Leave requests — balances calculated automatically'],
                'tech_stack' => 'Flutter, Laravel, PostgreSQL, Firebase',
                'product_url' => 'https://example.com/shiftdesk',
            ],
            [
                'name' => 'ClinicFlow',
                'tagline' => 'Appointments and patient records for clinics',
                'category' => 'SaaS',
                'status' => 'live',
                'summary' => 'Online booking, a live front-desk queue, digital prescriptions and follow-up reminders — designed for small and mid-sized clinics.',
                'features' => ['Online booking — patients pick a slot themselves', 'Live queue — the front desk sees who is next', 'Digital prescriptions — printable or sent by SMS', 'Follow-up reminders — fewer missed revisits'],
                'tech_stack' => 'Laravel, React, MySQL',
                'product_url' => 'https://example.com/clinicflow',
            ],
            [
                'name' => 'StockPilot',
                'tagline' => 'Multi-warehouse inventory with barcode scanning',
                'category' => 'Tool',
                'status' => 'coming_soon',
                'summary' => 'Track stock across warehouses, scan barcodes from any phone, and get reorder alerts before a best-seller runs out.',
                'features' => ['Barcode scanning — from any phone camera', 'Multiple warehouses — transfers with full history', 'Reorder alerts — based on real sales velocity', 'Supplier orders — raised straight from low-stock lists'],
                'tech_stack' => 'Laravel, Alpine.js, MySQL',
                'product_url' => null,
            ],
            [
                'name' => 'FieldNote',
                'tagline' => 'Job cards and sign-off for field service teams',
                'category' => 'Mobile app',
                'status' => 'beta',
                'summary' => 'Assign jobs to technicians, capture photos and notes on site, and collect the customer’s signature before leaving.',
                'features' => ['Job cards — assigned and tracked in real time', 'Photo notes — before and after, stamped with time', 'Customer sign-off — signature captured on the phone', 'Offline mode — syncs when signal comes back'],
                'tech_stack' => 'React Native, Laravel, MySQL',
                'product_url' => 'https://example.com/fieldnote',
            ],
        ];

        $created = 0;
        foreach ($products as $i => $data) {
            $slug = 'demo-'.strtolower($data['name']);
            if (Product::where('slug', $slug)->exists()) {
                continue;
            }

            Product::create([
                ...$data,
                'slug' => $slug,
                'features' => implode("\n", $data['features']),
                'description' => '<p>'.e($data['summary']).'</p><p><em>Demo product — sample content for previewing the layout.</em></p>',
                'logo' => $this->copy($slug, 'logo.webp'),
                'cover_image' => $this->copy($slug, 'cover.webp'),
                'cover_image_alt' => $data['name'].' home page mock-up',
                'is_published' => true,
                'is_featured' => false,
                'sort_order' => 10 + $i,
            ]);
            $created++;
        }

        $this->command?->info("Demo products created: {$created}.");
    }

    private function copy(string $slug, string $name): string
    {
        $target = "products/{$slug}/{$name}";
        Storage::disk('public')->put($target, file_get_contents(database_path("seeders/assets/products/{$slug}/{$name}")));

        return $target;
    }
}
