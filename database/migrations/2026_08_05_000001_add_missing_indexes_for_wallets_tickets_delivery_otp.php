<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // seller_wallets: looked up by seller_id on nearly every order-status transition
        // and vendor/freelancer dashboard load (several with lockForUpdate()); had only
        // the primary key, forcing a full table scan per lookup.
        if (Schema::hasTable('seller_wallets')) {
            Schema::table('seller_wallets', function (Blueprint $table) {
                $table->index('seller_id', 'seller_wallets_seller_id_index');
            });
        }

        // seller_wallet_histories: used as lookup/join keys for earnings history and
        // reporting; had only the primary key.
        if (Schema::hasTable('seller_wallet_histories')) {
            Schema::table('seller_wallet_histories', function (Blueprint $table) {
                $table->index('seller_id', 'seller_wallet_histories_seller_id_index');
                $table->index('order_id', 'seller_wallet_histories_order_id_index');
                $table->index('product_id', 'seller_wallet_histories_product_id_index');
            });
        }

        // support_tickets: the customer ticket-list page filters by customer_id and
        // sorts by created_at, both unindexed.
        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->index(['customer_id', 'created_at'], 'support_tickets_customer_created_index');
            });
        }

        // support_ticket_convs: every ticket-detail view does a full scan filtering by
        // support_ticket_id, which had no index at all.
        if (Schema::hasTable('support_ticket_convs')) {
            Schema::table('support_ticket_convs', function (Blueprint $table) {
                $table->index('support_ticket_id', 'support_ticket_convs_support_ticket_id_index');
            });
        }

        // delivery_histories: grows unbounded with delivery tracking pings, looked up by
        // order_id (+deliveryman_id) with no index at all.
        if (Schema::hasTable('delivery_histories')) {
            Schema::table('delivery_histories', function (Blueprint $table) {
                $table->index(['order_id', 'deliveryman_id'], 'delivery_histories_order_deliveryman_index');
            });
        }

        // phone_or_email_verifications: hit on every OTP/login-verification check via
        // phone_or_email, which had no index.
        if (Schema::hasTable('phone_or_email_verifications')) {
            Schema::table('phone_or_email_verifications', function (Blueprint $table) {
                $table->index('phone_or_email', 'phone_or_email_verifications_phone_or_email_index');
            });
        }

        // digital_product_otp_verifications: niche/low-traffic today but same lookup
        // pattern as phone_or_email_verifications; included for completeness.
        if (Schema::hasTable('digital_product_otp_verifications')) {
            Schema::table('digital_product_otp_verifications', function (Blueprint $table) {
                $table->index('order_details_id', 'digital_product_otp_verifications_order_details_id_index');
                $table->index('identity', 'digital_product_otp_verifications_identity_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('seller_wallets')) {
            $this->dropIndexIfExists('seller_wallets', 'seller_wallets_seller_id_index');
        }

        if (Schema::hasTable('seller_wallet_histories')) {
            $this->dropIndexIfExists('seller_wallet_histories', 'seller_wallet_histories_seller_id_index');
            $this->dropIndexIfExists('seller_wallet_histories', 'seller_wallet_histories_order_id_index');
            $this->dropIndexIfExists('seller_wallet_histories', 'seller_wallet_histories_product_id_index');
        }

        if (Schema::hasTable('support_tickets')) {
            $this->dropIndexIfExists('support_tickets', 'support_tickets_customer_created_index');
        }

        if (Schema::hasTable('support_ticket_convs')) {
            $this->dropIndexIfExists('support_ticket_convs', 'support_ticket_convs_support_ticket_id_index');
        }

        if (Schema::hasTable('delivery_histories')) {
            $this->dropIndexIfExists('delivery_histories', 'delivery_histories_order_deliveryman_index');
        }

        if (Schema::hasTable('phone_or_email_verifications')) {
            $this->dropIndexIfExists('phone_or_email_verifications', 'phone_or_email_verifications_phone_or_email_index');
        }

        if (Schema::hasTable('digital_product_otp_verifications')) {
            $this->dropIndexIfExists('digital_product_otp_verifications', 'digital_product_otp_verifications_order_details_id_index');
            $this->dropIndexIfExists('digital_product_otp_verifications', 'digital_product_otp_verifications_identity_index');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }
};
