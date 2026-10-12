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
                'name'           => 'Premium Wireless Headphones',
                'slug'           => 'premium-wireless-headphones-demo-' . $sellerId,
                'code'           => 'WH' . strtoupper(substr(md5('wh'.$sellerId), 0, 7)),
                'meta_title'     => 'Premium Wireless Headphones',
                'unit_price'     => 89.99,
                'purchase_price' => 45.00,
                'discount'       => 10,
                'discount_type'  => 'percent',
                'current_stock'  => 50,
                'featured_status'=> 1,
                'thumbnail'      => 'headphones.webp',
                'details'        => '<p>Flagship wireless over-ear headphones with hybrid active noise cancellation, 40mm custom drivers and 30-hour battery life. Memory-foam earcups, foldable design and multipoint Bluetooth 5.3 for seamless switching between devices.</p>',
            ],
            [
                'name'           => 'Smart LED Desk Lamp',
                'slug'           => 'smart-led-desk-lamp-demo-' . $sellerId,
                'code'           => 'LAMP' . strtoupper(substr(md5('lamp'.$sellerId), 0, 6)),
                'meta_title'     => 'Smart LED Desk Lamp',
                'unit_price'     => 34.99,
                'purchase_price' => 15.00,
                'tax'            => 5,
                'discount'       => 5,
                'discount_type'  => 'flat',
                'current_stock'  => 120,
                'free_shipping'  => 1,
                'shipping_cost'  => 0,
                'thumbnail'      => 'desk-lamp.webp',
                'details'        => '<p>Architect-style smart desk lamp with 10-step dimming, 5 colour temperatures (2700K–6000K) and USB-C power. Touch-sensitive controls, 60-minute auto-off timer and flicker-free eye-care lighting certified for long work sessions.</p>',
            ],
            [
                'name'           => 'Ergonomic Office Chair',
                'slug'           => 'ergonomic-office-chair-demo-' . $sellerId,
                'code'           => 'CHAIR' . strtoupper(substr(md5('chair'.$sellerId), 0, 5)),
                'meta_title'     => 'Ergonomic Office Chair',
                'unit_price'     => 249.00,
                'purchase_price' => 120.00,
                'discount'       => 15,
                'discount_type'  => 'percent',
                'current_stock'  => 20,
                'shipping_cost'  => 20,
                'featured_status'=> 1,
                'refundable'     => 0,
                'thumbnail'      => 'office-chair.webp',
                'details'        => '<p>Full-mesh ergonomic task chair with dynamic lumbar support, 4D adjustable armrests, synchro-tilt mechanism and height-adjustable headrest. BIFMA-certified frame rated for 150kg, designed for 8+ hour workdays.</p>',
            ],
            [
                'name'           => 'Portable Bluetooth Speaker',
                'slug'           => 'portable-bluetooth-speaker-demo-' . $sellerId,
                'code'           => 'SPK' . strtoupper(substr(md5('spk'.$sellerId), 0, 7)),
                'meta_title'     => 'Portable Bluetooth Speaker',
                'unit_price'     => 59.99,
                'purchase_price' => 28.00,
                'tax'            => 5,
                'current_stock'  => 75,
                'free_shipping'  => 1,
                'shipping_cost'  => 0,
                'thumbnail'      => 'speaker.webp',
                'details'        => '<p>Compact Bluetooth 5.3 speaker with 360° immersive sound, deep-bass passive radiators and IPX7 waterproofing. 12-hour battery, TWS stereo pairing and built-in microphone for hands-free calls — built for outdoors.</p>',
            ],
            [
                'name'                 => 'Web App Development Guide (E-Book)',
                'slug'                 => 'web-app-development-guide-demo-' . $sellerId,
                'code'                 => 'EBOOK' . strtoupper(substr(md5('ebook'.$sellerId), 0, 5)),
                'meta_title'           => 'Web App Development Guide E-Book',
                'product_type'         => 'digital',
                'digital_product_type' => 'ready_product',
                'category_id'          => $digitalCatId,
                'sub_category_id'      => null,
                'sub_sub_category_id'  => null,
                'category_ids'         => json_encode([
                    ['id' => (string)$digitalCatId, 'position' => 0],
                ]),
                'unit_price'           => 19.99,
                'purchase_price'       => 0,
                'current_stock'        => 9999,
                'free_shipping'        => 1,
                'shipping_cost'        => 0,
                'refundable'           => 0,
                'thumbnail'            => 'ebook-guide.webp',
                'details'              => '<p>320-page hands-on guide to building production web apps: Laravel 10 + React, REST API design, authentication, testing, CI/CD and AWS deployment. Includes complete starter repo and lifetime updates. Instant download after purchase.</p>',
            ],
            [
                'name'           => 'Mechanical Gaming Keyboard',
                'slug'           => 'mechanical-gaming-keyboard-demo-' . $sellerId,
                'code'           => 'KB' . strtoupper(substr(md5('kb'.$sellerId), 0, 8)),
                'meta_title'     => 'Mechanical Gaming Keyboard',
                'unit_price'     => 119.00,
                'purchase_price' => 60.00,
                'discount'       => 20,
                'discount_type'  => 'flat',
                'current_stock'  => 35,
                'featured_status'=> 1,
                'thumbnail'      => 'keyboard.webp',
                'details'        => '<p>Tenkeyless mechanical keyboard with hot-swappable Cherry MX Blue switches, per-key RGB, PBT double-shot keycaps and CNC aluminium frame. Detachable USB-C, N-key rollover and 4000Hz polling for competitive play.</p>',
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
