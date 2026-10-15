<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * FinXCart production catalog mirror (finxcart.com).
 *
 * Seeds the real category tree and product catalog from the live FinXCart
 * marketplace so any fresh install / staging DB matches production structure.
 * All products are digital (instant delivery).
 */
class FinxCartCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categories = [
            ['name' => 'Marketplace', 'slug' => 'marketplace', 'position' => 0],
            ['name' => 'Solutions', 'slug' => 'solutions', 'position' => 1, 'children' => [
                ['name' => 'CRM Solutions', 'slug' => 'crm-solutions'],
                ['name' => 'White Label Solutions', 'slug' => 'white-label-solutions'],
                ['name' => 'Blockchain Solutions', 'slug' => 'blockchain-solutions'],
                ['name' => 'Token & Coin Services', 'slug' => 'token-coin-services'],
                ['name' => 'ICO / IEO / IDO Solutions', 'slug' => 'ico-ieo-ido-solutions'],
                ['name' => 'Compliance Solutions', 'slug' => 'compliance-solutions'],
                ['name' => 'Business Setup', 'slug' => 'business-setup'],
            ]],
            ['name' => 'Providers', 'slug' => 'providers', 'position' => 2],
            ['name' => 'Technology', 'slug' => 'technology', 'position' => 3],
            ['name' => 'Development', 'slug' => 'development', 'position' => 4],
            ['name' => 'Services', 'slug' => 'services', 'position' => 5],
            ['name' => 'Marketing', 'slug' => 'marketing', 'position' => 6],
            ['name' => 'Influencers', 'slug' => 'influencers', 'position' => 7],
            ['name' => 'Education', 'slug' => 'education', 'position' => 8],
            ['name' => 'Events', 'slug' => 'events', 'position' => 9],
            ['name' => 'Hire Freelancer', 'slug' => 'hire-freelancer', 'position' => 10],
        ];

        $catIds = [];
        foreach ($categories as $cat) {
            $children = $cat['children'] ?? [];
            unset($cat['children']);
            $id = DB::table('categories')->where('slug', $cat['slug'])->value('id')
                ?? DB::table('categories')->insertGetId(array_merge($cat, [
                    'home_status' => 1, 'created_at' => $now, 'updated_at' => $now,
                ]));
            // Ensure visible on home
            DB::table('categories')->where('id', $id)->update(['home_status' => 1, 'position' => $cat['position']]);
            $catIds[$cat['slug']] = $id;

            foreach ($children as $i => $child) {
                $cid = DB::table('categories')->where('slug', $child['slug'])->value('id')
                    ?? DB::table('categories')->insertGetId(array_merge($child, [
                        'parent_id' => $id, 'position' => $i, 'home_status' => 1,
                        'created_at' => $now, 'updated_at' => $now,
                    ]));
                $catIds[$child['slug']] = $cid;
            }
        }

        // Products mirrored from finxcart.com (USD). Category slugs map to above.
        $products = [
            ['name' => 'Client Onboarding Workflow', 'slug' => 'client-onboarding-workflow', 'price' => 1499.00, 'discount' => 749.50, 'cat' => 'compliance-solutions'],
            ['name' => 'Fintech Security Shield', 'slug' => 'fintech-security-shield', 'price' => 699.00, 'cat' => 'technology'],
            ['name' => 'Custom API Development', 'slug' => 'custom-api-development', 'price' => 1499.00, 'cat' => 'development'],
            ['name' => 'Global Payment API', 'slug' => 'global-payment-api', 'price' => 1499.00, 'cat' => 'technology'],
            ['name' => 'Trading Signals Hub', 'slug' => 'trading-signals-hub', 'price' => 79.00, 'cat' => 'services'],
            ['name' => 'Trading PDFs', 'slug' => 'trading-pdfs', 'price' => 39.00, 'cat' => 'education'],
            ['name' => 'Trading eBooks', 'slug' => 'trading-ebooks', 'price' => 59.00, 'cat' => 'education'],
            ['name' => 'Whitepaper Writing', 'slug' => 'whitepaper-writing', 'price' => 799.00, 'cat' => 'marketing'],
            ['name' => 'Pitch Deck Design', 'slug' => 'pitch-deck-design', 'price' => 499.00, 'cat' => 'marketing'],
            ['name' => 'Branding Packages', 'slug' => 'branding-packages', 'price' => 799.00, 'cat' => 'marketing'],
            ['name' => 'Logo Design', 'slug' => 'logo-design', 'price' => 249.00, 'cat' => 'marketing'],
            ['name' => 'Prop Firm Templates', 'slug' => 'prop-firm-templates', 'price' => 149.00, 'cat' => 'development'],
            ['name' => 'AI Trading Tools', 'slug' => 'ai-trading-tools', 'price' => 49.00, 'cat' => 'technology'],
            ['name' => 'Forex CRM', 'slug' => 'forex-crm', 'price' => 750.00, 'cat' => 'crm-solutions'],
            ['name' => 'Prop Firm CRM', 'slug' => 'prop-firm-crm', 'price' => 650.00, 'cat' => 'crm-solutions'],
            ['name' => 'All In One CRM', 'slug' => 'all-in-one-crm', 'price' => 0, 'cat' => 'crm-solutions'],
            ['name' => 'Trading Contest CRM', 'slug' => 'trading-contest-crm', 'price' => 600.00, 'cat' => 'crm-solutions'],
            ['name' => 'Bridging Academy Products', 'slug' => 'bridging-academy-products', 'price' => 1000.00, 'cat' => 'education'],
            ['name' => 'Prop Firm Website Development', 'slug' => 'prop-firm-website-development', 'price' => 1200.00, 'cat' => 'development'],
            ['name' => 'Broker Website Development', 'slug' => 'broker-website-development', 'price' => 1300.00, 'cat' => 'development'],
            ['name' => 'Website Templates', 'slug' => 'website-templates', 'price' => 59.00, 'cat' => 'development'],
        ];

        // Seller/shop for fixtures: reuse id 1 when present (local demo).
        $sellerId = DB::table('sellers')->orderBy('id')->value('id') ?? 1;
        $shopId = DB::table('shops')->orderBy('id')->value('id') ?? 1;

        foreach ($products as $p) {
            if (DB::table('products')->where('slug', $p['slug'])->exists()) {
                continue;
            }
            $productId = DB::table('products')->insertGetId([
                'name' => $p['name'],
                'slug' => $p['slug'],
                'added_by' => 'seller',
                'user_id' => $sellerId,
                'category_id' => $catIds[$p['cat']] ?? $catIds['marketplace'],
                'unit_price' => $p['price'],
                'discount' => $p['discount'] ?? 0,
                'discount_type' => isset($p['discount']) ? 'flat' : 'percent',
                'status' => 1,
                'request_status' => 1,
                'product_type' => 'digital',
                'digital_product_type' => 'ready_product',
                'current_stock' => 999,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->command->info("  Created #{$productId}: {$p['name']}");
        }

        $this->command->info('FinXCart catalog mirror seeded.');

        // Official logo (from finxcart.com) — white FX mark for dark headers.
        $logoFile = 'finxcart-logo-official.webp';
        foreach (['company_web_logo', 'company_footer_logo', 'company_mobile_logo'] as $logoKey) {
            $exists = DB::table('business_settings')->where('key_name', $logoKey)->exists();
            if ($exists) {
                DB::table('business_settings')->where('key_name', $logoKey)->update([
                    'value' => $logoFile, 'updated_at' => $now,
                ]);
            } else {
                DB::table('business_settings')->insert([
                    'key_name' => $logoKey, 'value' => $logoFile,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
        $this->command->info('Official logo set.');
    }
}
