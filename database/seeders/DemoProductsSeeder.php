<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoProductsSeeder extends Seeder
{
    public function run(): void
    {
        $emails = [
            'rogstrix2514@gmail.com',
            'ragnarvlothbrok012@gmail.com',
        ];

        foreach ($emails as $email) {
            $seller = DB::table('sellers')->where('email', $email)->first();
            if (!$seller) {
                $this->command->warn("Seller {$email} not found — skipping.");
                continue;
            }
            $this->seedForSeller($seller->id);
        }
    }

    private function seedForSeller(int $sellerId): void
    {
        $now = now();

        // Remove existing demo products for clean re-seed
        DB::table('products')->where('user_id', $sellerId)->delete();

        // Resolve categories by name (works on any DB, not just production IDs)
        $catId = DB::table('categories')->where('name', 'Test Category')->value('id')
            ?? DB::table('categories')->insertGetId(['name' => 'Electronics', 'slug' => 'electronics', 'position' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $digitalCatId = DB::table('categories')->where('name', 'Digital Products')->value('id')
            ?? DB::table('categories')->insertGetId(['name' => 'Digital Products', 'slug' => 'digital-products', 'position' => 1, 'created_at' => $now, 'updated_at' => $now]);

        $base = [
            'added_by'                 => 'seller',
            'user_id'                  => $sellerId,
            'category_id'              => $catId,
            'sub_category_id'          => null,
            'sub_sub_category_id'      => null,
            'category_ids'             => json_encode([
                ['id' => (string)$catId, 'position' => 0],
            ]),
            'unit'                     => 'pcs',
            'min_qty'                  => 1,
            'images'                   => json_encode([]),
            'color_image'              => json_encode([]),
            'thumbnail'                => '', // set per-product below
            'thumbnail_storage_type'   => 'public',
            'colors'                   => json_encode([]),
            'attributes'               => json_encode([]),
            'choice_options'           => json_encode([]),
            'variation'                => json_encode([]),
            'product_type'             => 'physical',
            'published'                => 1,
            'status'                   => 1,
            'featured_status'          => 0,
            'request_status'           => 1,
            'tax'                      => 0,
            'tax_type'                 => 'percent',
            'tax_model'                => 'exclude',
            'discount'                 => 0,
            'discount_type'            => 'flat',
            'free_shipping'            => 0,
            'shipping_cost'            => 5,
            'refundable'               => 1,
            'multiply_qty'             => 0,
            'minimum_order_qty'        => 1,
            'is_shipping_cost_updated' => 0,
            'created_at'               => $now,
            'updated_at'               => $now,
        ];

        $products = [
            [
                'name'           => 'Pro Trading Terminal',
                'slug'           => 'pro-trading-terminal-demo-' . $sellerId,
                'code'           => 'TT' . strtoupper(substr(md5('tt'.$sellerId), 0, 7)),
                'meta_title'     => 'Pro Trading Terminal',
                'unit_price'     => 299.00,
                'purchase_price' => 150.00,
                'discount'       => 10,
                'discount_type'  => 'percent',
                'current_stock'  => 9999,
                'featured_status'=> 1,
                'thumbnail'      => 'ft-terminal.webp',
                'details'        => '<p>Professional multi-asset trading terminal with real-time charts, one-click execution, advanced order types and custom indicators. MT4/MT5 bridge included. Lifetime license with free updates.</p>',
            ],
            [
                'name'           => 'Smart Trade Copier',
                'slug'           => 'smart-trade-copier-demo-' . $sellerId,
                'code'           => 'TC' . strtoupper(substr(md5('tc'.$sellerId), 0, 7)),
                'meta_title'     => 'Smart Trade Copier',
                'unit_price'     => 149.00,
                'purchase_price' => 70.00,
                'discount'       => 0,
                'discount_type'  => 'flat',
                'current_stock'  => 9999,
                'featured_status'=> 1,
                'thumbnail'      => 'ft-copier.webp',
                'details'        => '<p>Copy trades across unlimited MT4/MT5 accounts with lot-size scaling, risk filters and reverse-copy mode. Sub-100ms execution. Includes VPS setup guide and priority support.</p>',
            ],
            [
                'name'           => 'Broker CRM Suite',
                'slug'           => 'broker-crm-suite-demo-' . $sellerId,
                'code'           => 'CRM' . strtoupper(substr(md5('crm'.$sellerId), 0, 6)),
                'meta_title'     => 'Broker CRM Suite',
                'unit_price'     => 499.00,
                'purchase_price' => 250.00,
                'discount'       => 15,
                'discount_type'  => 'percent',
                'current_stock'  => 9999,
                'featured_status'=> 1,
                'thumbnail'      => 'ft-crm.webp',
                'details'        => '<p>Complete CRM for forex brokerages: client onboarding, KYC workflows, IB management, ticket system and marketing automation. White-label ready. Annual license.</p>',
            ],
            [
                'name'           => 'Payments API Gateway',
                'slug'           => 'payments-api-gateway-demo-' . $sellerId,
                'code'           => 'API' . strtoupper(substr(md5('api'.$sellerId), 0, 6)),
                'meta_title'     => 'Payments API Gateway',
                'unit_price'     => 199.00,
                'purchase_price' => 90.00,
                'discount'       => 0,
                'discount_type'  => 'flat',
                'current_stock'  => 9999,
                'featured_status'=> 1,
                'thumbnail'      => 'ft-payments.webp',
                'details'        => '<p>Developer-first payments API for brokerages: accept cards, crypto and bank transfers with a single integration. Webhooks, sandbox environment and full documentation included.</p>',
            ],
            [
                'name'           => 'Risk Management Dashboard',
                'slug'           => 'risk-management-dashboard-demo-' . $sellerId,
                'code'           => 'RISK' . strtoupper(substr(md5('risk'.$sellerId), 0, 5)),
                'meta_title'     => 'Risk Management Dashboard',
                'unit_price'     => 249.00,
                'purchase_price' => 120.00,
                'discount'       => 0,
                'discount_type'  => 'flat',
                'current_stock'  => 9999,
                'featured_status'=> 0,
                'thumbnail'      => 'ft-risk.webp',
                'details'        => '<p>Real-time risk analytics for trading operations: exposure monitoring, margin alerts, P&L attribution and automated kill-switches. Integrates with MT4/MT5 Manager API.</p>',
            ],
            [
                'name'           => 'Forex Signals App',
                'slug'           => 'forex-signals-app-demo-' . $sellerId,
                'code'           => 'SIG' . strtoupper(substr(md5('sig'.$sellerId), 0, 6)),
                'meta_title'     => 'Forex Signals App',
                'unit_price'     => 49.00,
                'purchase_price' => 20.00,
                'discount'       => 20,
                'discount_type'  => 'percent',
                'current_stock'  => 9999,
                'featured_status'=> 0,
                'thumbnail'      => 'ft-signals.webp',
                'details'        => '<p>Mobile app delivering curated forex signals with entry, stop-loss and take-profit levels. Push notifications, performance tracking and analyst commentary. Monthly subscription.</p>',
            ],
        ];

        foreach ($products as $data) {
            $row = array_merge($base, $data);
            $id  = DB::table('products')->insertGetId($row);
            $this->command->info("  Created #{$id}: {$row['name']}");
        }

        $this->command->info("Done — " . count($products) . " products added for seller #{$sellerId}.");
    }
}
