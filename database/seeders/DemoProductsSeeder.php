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

        $base = [
            'added_by'                 => 'seller',
            'user_id'                  => $sellerId,
            'category_id'              => 342,
            'sub_category_id'          => 355, // Trending Services (under Home)
            'sub_sub_category_id'      => null,
            'category_ids'             => json_encode([
                ['id' => '342', 'position' => 0],
                ['id' => '355', 'position' => 1],
            ]),
            'unit'                     => 'pcs',
            'min_qty'                  => 1,
            'images'                   => json_encode([]),
            'color_image'              => json_encode([]),
            'thumbnail'                => '2025-05-21-682dd03902b35.webp', // existing product image on disk
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
                'details'        => '<p>High-quality wireless headphones with active noise cancellation and 30-hour battery life. Foldable, lightweight design with premium drivers for studio-quality sound.</p>',
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
                'details'        => '<p>USB-C powered smart desk lamp with adjustable brightness (10 levels) and colour temperature. Touch control, memory function, and eye-care mode included.</p>',
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
                'details'        => '<p>Lumbar support, adjustable armrests, tilt-lock mechanism, and breathable mesh back. Designed for long work sessions with maximum comfort.</p>',
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
                'details'        => '<p>360-degree surround sound, IPX7 waterproof rating, 12-hour playtime. Pair two together for stereo mode. Ideal for outdoor use, camping, and beach trips.</p>',
            ],
            [
                'name'                 => 'Web App Development Guide (E-Book)',
                'slug'                 => 'web-app-development-guide-demo-' . $sellerId,
                'code'                 => 'EBOOK' . strtoupper(substr(md5('ebook'.$sellerId), 0, 5)),
                'meta_title'           => 'Web App Development Guide E-Book',
                'product_type'         => 'digital',
                'digital_product_type' => 'ready_product',
                'category_id'          => 343,  // Marketplace
                'sub_category_id'      => 382,  // Trading Platforms
                'sub_sub_category_id'  => null,
                'category_ids'         => json_encode([
                    ['id' => '343', 'position' => 0],
                    ['id' => '382', 'position' => 1],
                ]),
                'unit_price'           => 19.99,
                'purchase_price'       => 0,
                'current_stock'        => 9999,
                'free_shipping'        => 1,
                'shipping_cost'        => 0,
                'refundable'           => 0,
                'details'              => '<p>300-page comprehensive guide covering Laravel 10, React, REST APIs, deployment strategies, and AWS cloud architecture. Instant download after purchase.</p>',
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
                'details'        => '<p>TKL layout with Cherry MX Blue switches, per-key RGB backlighting, and USB-C detachable cable. N-key rollover, anti-ghosting, aluminium top plate.</p>',
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
