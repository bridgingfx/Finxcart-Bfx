<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // orders: order list filters/joins by these columns on admin, vendor, and
        // customer order pages; only customer_id/order_status/created_at were indexed so far.
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('payment_status', 'orders_payment_status_index');
                $table->index('delivery_man_id', 'orders_delivery_man_id_index');
                $table->index('shipping_method_id', 'orders_shipping_method_id_index');
                $table->index('coupon_code', 'orders_coupon_code_index');
                $table->index('order_group_id', 'orders_order_group_id_index');
                $table->index('verification_status', 'orders_verification_status_index');
                $table->index('billing_address', 'orders_billing_address_index');
            });
        }

        // order_details: used to filter refund requests and per-item delivery status.
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->index('shipping_method_id', 'order_details_shipping_method_id_index');
                $table->index('refund_request', 'order_details_refund_request_index');
                $table->index('delivery_status', 'order_details_delivery_status_index');
            });
        }

        // products: slug backs every product-detail-page lookup; the others gate
        // storefront listing/search filters.
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('slug', 'products_slug_index');
                $table->index('featured_status', 'products_featured_status_index');
                $table->index('flash_deal', 'products_flash_deal_index');
                $table->index('published', 'products_published_index');
                $table->index('code', 'products_code_index');
            });
        }

        // categories: slug backs category-page lookups; parent_id/home_status/priority
        // back category-tree building and homepage section ordering.
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('slug', 'categories_slug_index');
                $table->index('parent_id', 'categories_parent_id_index');
                $table->index('home_status', 'categories_home_status_index');
                $table->index('priority', 'categories_priority_index');
            });
        }

        // category_product: prevent duplicate category assignments per product and
        // give category-listing queries a covering composite index.
        if (Schema::hasTable('category_product')) {
            Schema::table('category_product', function (Blueprint $table) {
                $table->unique(['product_id', 'category_id'], 'category_product_product_id_category_id_unique');
            });
        }

        // shops: slug backs every shop-page URL lookup.
        if (Schema::hasTable('shops')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->index('slug', 'shops_slug_index');
            });
        }

        // reviews: order_id backs the "has this order been reviewed" check.
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index('order_id', 'reviews_order_id_index');
            });
        }

        // carts: product_id/seller_id back cart-merge and seller-scoped cart queries.
        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->index('product_id', 'carts_product_id_index');
                $table->index('seller_id', 'carts_seller_id_index');
            });
        }

        // coupons: code is looked up on every checkout attempt; the rest scope
        // coupon listings by seller/customer/status/expiry.
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->index('code', 'coupons_code_index');
                $table->index('seller_id', 'coupons_seller_id_index');
                $table->index('customer_id', 'coupons_customer_id_index');
                $table->index('status', 'coupons_status_index');
                $table->index('expire_date', 'coupons_expire_date_index');
            });
        }

        // flash_deals: status/date-range back the "active deals" query run on
        // most storefront pages; slug/product_id back deal and product lookups.
        if (Schema::hasTable('flash_deals')) {
            Schema::table('flash_deals', function (Blueprint $table) {
                $table->index('status', 'flash_deals_status_index');
                $table->index(['start_date', 'end_date'], 'flash_deals_start_end_date_index');
                $table->index('slug', 'flash_deals_slug_index');
                $table->index('product_id', 'flash_deals_product_id_index');
            });
        }

        if (Schema::hasTable('flash_deal_products')) {
            Schema::table('flash_deal_products', function (Blueprint $table) {
                $table->index('flash_deal_id', 'flash_deal_products_flash_deal_id_index');
                $table->index('product_id', 'flash_deal_products_product_id_index');
            });
        }

        // withdraw_requests: no index existed on any FK/status column despite
        // being filtered by all of these across admin/vendor withdrawal pages.
        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                $table->index('seller_id', 'withdraw_requests_seller_id_index');
                $table->index('delivery_man_id', 'withdraw_requests_delivery_man_id_index');
                $table->index('admin_id', 'withdraw_requests_admin_id_index');
                $table->index('withdrawal_method_id', 'withdraw_requests_withdrawal_method_id_index');
                $table->index('delivery_status', 'withdraw_requests_delivery_status_index');
                $table->index('approved', 'withdraw_requests_approved_index');
            });
        }

        // transactions: no index existed on any FK/status column despite being
        // filtered by all of these on customer/vendor transaction history pages.
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('order_id', 'transactions_order_id_index');
                $table->index('payer_id', 'transactions_payer_id_index');
                $table->index('payment_receiver_id', 'transactions_payment_receiver_id_index');
                $table->index('order_details_id', 'transactions_order_details_id_index');
                $table->index('payment_status', 'transactions_payment_status_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_payment_status_index');
                $table->dropIndex('orders_delivery_man_id_index');
                $table->dropIndex('orders_shipping_method_id_index');
                $table->dropIndex('orders_coupon_code_index');
                $table->dropIndex('orders_order_group_id_index');
                $table->dropIndex('orders_verification_status_index');
                $table->dropIndex('orders_billing_address_index');
            });
        }

        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->dropIndex('order_details_shipping_method_id_index');
                $table->dropIndex('order_details_refund_request_index');
                $table->dropIndex('order_details_delivery_status_index');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_slug_index');
                $table->dropIndex('products_featured_status_index');
                $table->dropIndex('products_flash_deal_index');
                $table->dropIndex('products_published_index');
                $table->dropIndex('products_code_index');
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_slug_index');
                $table->dropIndex('categories_parent_id_index');
                $table->dropIndex('categories_home_status_index');
                $table->dropIndex('categories_priority_index');
            });
        }

        if (Schema::hasTable('category_product')) {
            Schema::table('category_product', function (Blueprint $table) {
                $table->dropUnique('category_product_product_id_category_id_unique');
            });
        }

        if (Schema::hasTable('shops')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropIndex('shops_slug_index');
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex('reviews_order_id_index');
            });
        }

        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropIndex('carts_product_id_index');
                $table->dropIndex('carts_seller_id_index');
            });
        }

        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropIndex('coupons_code_index');
                $table->dropIndex('coupons_seller_id_index');
                $table->dropIndex('coupons_customer_id_index');
                $table->dropIndex('coupons_status_index');
                $table->dropIndex('coupons_expire_date_index');
            });
        }

        if (Schema::hasTable('flash_deals')) {
            Schema::table('flash_deals', function (Blueprint $table) {
                $table->dropIndex('flash_deals_status_index');
                $table->dropIndex('flash_deals_start_end_date_index');
                $table->dropIndex('flash_deals_slug_index');
                $table->dropIndex('flash_deals_product_id_index');
            });
        }

        if (Schema::hasTable('flash_deal_products')) {
            Schema::table('flash_deal_products', function (Blueprint $table) {
                $table->dropIndex('flash_deal_products_flash_deal_id_index');
                $table->dropIndex('flash_deal_products_product_id_index');
            });
        }

        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                $table->dropIndex('withdraw_requests_seller_id_index');
                $table->dropIndex('withdraw_requests_delivery_man_id_index');
                $table->dropIndex('withdraw_requests_admin_id_index');
                $table->dropIndex('withdraw_requests_withdrawal_method_id_index');
                $table->dropIndex('withdraw_requests_delivery_status_index');
                $table->dropIndex('withdraw_requests_approved_index');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('transactions_order_id_index');
                $table->dropIndex('transactions_payer_id_index');
                $table->dropIndex('transactions_payment_receiver_id_index');
                $table->dropIndex('transactions_order_details_id_index');
                $table->dropIndex('transactions_payment_status_index');
            });
        }
    }
};
