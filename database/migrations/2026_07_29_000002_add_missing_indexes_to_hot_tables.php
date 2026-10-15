<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // reviews: product page rating widget filters product_id+status together;
        // delivery-man rating page and per-customer "already reviewed" checks filter
        // customer_id / delivery_man_id alone. Table currently has no index besides id.
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index(['product_id', 'status'], 'reviews_product_id_status_index');
                $table->index('customer_id', 'reviews_customer_id_index');
                $table->index('delivery_man_id', 'reviews_delivery_man_id_index');
            });
        }

        // wishlists: "is this in my wishlist" is checked on nearly every product card,
        // always filtering customer_id + product_id together. Only id is indexed today.
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->index(['customer_id', 'product_id'], 'wishlists_customer_id_product_id_index');
            });
        }

        // refund_transactions: only id is indexed today; order_id/payer_id/payment_receiver_id
        // and order_details_id are all used to look up a customer's or seller's transactions.
        if (Schema::hasTable('refund_transactions')) {
            Schema::table('refund_transactions', function (Blueprint $table) {
                $table->index('order_id', 'refund_transactions_order_id_index');
                $table->index('payer_id', 'refund_transactions_payer_id_index');
                $table->index('payment_receiver_id', 'refund_transactions_payment_receiver_id_index');
                $table->index('order_details_id', 'refund_transactions_order_details_id_index');
            });
        }

        // refund_requests: order_id/status already indexed; order_details_id/customer_id/
        // product_id are not, despite being used to join back to the order and to list a
        // customer's own refund requests.
        if (Schema::hasTable('refund_requests')) {
            Schema::table('refund_requests', function (Blueprint $table) {
                $table->index('order_details_id', 'refund_requests_order_details_id_index');
                $table->index('customer_id', 'refund_requests_customer_id_index');
                $table->index('product_id', 'refund_requests_product_id_index');
            });
        }

        // freelancer_contract_reviews: contract_id/reviewer_type already indexed;
        // reviewer_id/reviewee_id are not, despite being used to aggregate a seller's
        // or customer's own review history.
        if (Schema::hasTable('freelancer_contract_reviews')) {
            Schema::table('freelancer_contract_reviews', function (Blueprint $table) {
                $table->index('reviewer_id', 'freelancer_contract_reviews_reviewer_id_index');
                $table->index('reviewee_id', 'freelancer_contract_reviews_reviewee_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex('reviews_product_id_status_index');
                $table->dropIndex('reviews_customer_id_index');
                $table->dropIndex('reviews_delivery_man_id_index');
            });
        }

        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->dropIndex('wishlists_customer_id_product_id_index');
            });
        }

        if (Schema::hasTable('refund_transactions')) {
            Schema::table('refund_transactions', function (Blueprint $table) {
                $table->dropIndex('refund_transactions_order_id_index');
                $table->dropIndex('refund_transactions_payer_id_index');
                $table->dropIndex('refund_transactions_payment_receiver_id_index');
                $table->dropIndex('refund_transactions_order_details_id_index');
            });
        }

        if (Schema::hasTable('refund_requests')) {
            Schema::table('refund_requests', function (Blueprint $table) {
                $table->dropIndex('refund_requests_order_details_id_index');
                $table->dropIndex('refund_requests_customer_id_index');
                $table->dropIndex('refund_requests_product_id_index');
            });
        }

        if (Schema::hasTable('freelancer_contract_reviews')) {
            Schema::table('freelancer_contract_reviews', function (Blueprint $table) {
                $table->dropIndex('freelancer_contract_reviews_reviewer_id_index');
                $table->dropIndex('freelancer_contract_reviews_reviewee_id_index');
            });
        }
    }
};
