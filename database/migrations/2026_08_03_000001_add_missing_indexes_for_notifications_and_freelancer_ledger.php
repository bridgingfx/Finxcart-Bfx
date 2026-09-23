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
        // admin_notifications: the unread bell count/list runs on every single admin
        // page load (layouts/admin/partials/_header.blade.php) with no index on read_at.
        if (Schema::hasTable('admin_notifications')) {
            Schema::table('admin_notifications', function (Blueprint $table) {
                $table->index('read_at', 'admin_notifications_read_at_index');
            });
        }

        // chattings: the admin unseen-message badge (admin_id + notification_receiver +
        // seen_by_admin) also runs on every admin page load with only seller_id indexed.
        // user_id/delivery_man_id back the customer/delivery-man mobile chat API polling.
        if (Schema::hasTable('chattings')) {
            Schema::table('chattings', function (Blueprint $table) {
                $table->index(['admin_id', 'notification_receiver', 'seen_by_admin'], 'chattings_admin_notification_seen_index');
                $table->index('user_id', 'chattings_user_id_index');
                $table->index('delivery_man_id', 'chattings_delivery_man_id_index');
            });
        }

        // vendor_notifications: shared by both the vendor and freelancer panel layout
        // composers, run on every vendor/freelancer page load; only seller_id was indexed.
        if (Schema::hasTable('vendor_notifications')) {
            Schema::table('vendor_notifications', function (Blueprint $table) {
                $table->index(['seller_id', 'read_at', 'created_at'], 'vendor_notifications_seller_read_created_index');
            });
        }

        // customer_notifications: the storefront header composer runs this on every
        // logged-in customer page load; only customer_id was indexed. customer_id also
        // carries a real FK constraint, and this composite's leftmost column is
        // customer_id, so InnoDB may retire the old FK-supporting single-column index
        // in favor of this one — that's fine, this composite still covers it.
        if (Schema::hasTable('customer_notifications')) {
            Schema::table('customer_notifications', function (Blueprint $table) {
                $table->index(['customer_id', 'created_at'], 'customer_notifications_customer_created_index');
            });
        }

        // commission_ledger: reference_type (used to isolate freelancer-contract rows
        // from product-order rows) had no index at all, standalone or composite.
        // seller_id also carries a real FK constraint; same InnoDB note as above applies.
        if (Schema::hasTable('commission_ledger')) {
            Schema::table('commission_ledger', function (Blueprint $table) {
                $table->index(['seller_id', 'reference_type', 'created_at'], 'commission_ledger_seller_reftype_created_index');
                $table->index(['reference_type', 'id'], 'commission_ledger_reftype_id_index');
            });
        }

        // freelancer_contract_reviews / freelancer_reviews: the admin reviews moderation
        // page sorts the whole table by created_at with no filter and no created_at index.
        if (Schema::hasTable('freelancer_contract_reviews')) {
            Schema::table('freelancer_contract_reviews', function (Blueprint $table) {
                $table->index('created_at', 'freelancer_contract_reviews_created_at_index');
            });
        }

        if (Schema::hasTable('freelancer_reviews')) {
            Schema::table('freelancer_reviews', function (Blueprint $table) {
                $table->index('created_at', 'freelancer_reviews_created_at_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admin_notifications')) {
            $this->dropIndexIfExists('admin_notifications', 'admin_notifications_read_at_index');
        }

        if (Schema::hasTable('chattings')) {
            $this->dropIndexIfExists('chattings', 'chattings_admin_notification_seen_index');
            $this->dropIndexIfExists('chattings', 'chattings_user_id_index');
            $this->dropIndexIfExists('chattings', 'chattings_delivery_man_id_index');
        }

        if (Schema::hasTable('vendor_notifications')) {
            $this->dropIndexIfExists('vendor_notifications', 'vendor_notifications_seller_read_created_index');
        }

        // customer_id carries a real FK constraint and InnoDB may have retired the old
        // single-column supporting index in favor of the composite this migration added
        // (see up()) — restore a plain customer_id index first so InnoDB always has a
        // supporting index for the constraint before the composite is dropped.
        if (Schema::hasTable('customer_notifications')) {
            DB::statement('CREATE INDEX IF NOT EXISTS customer_notifications_customer_id_index ON customer_notifications (customer_id)');
            $this->dropIndexIfExists('customer_notifications', 'customer_notifications_customer_created_index');
        }

        // seller_id carries a real FK constraint — same defensive restore as above.
        if (Schema::hasTable('commission_ledger')) {
            DB::statement('CREATE INDEX IF NOT EXISTS commission_ledger_seller_id_index_restored ON commission_ledger (seller_id)');
            $this->dropIndexIfExists('commission_ledger', 'commission_ledger_seller_reftype_created_index');
            $this->dropIndexIfExists('commission_ledger', 'commission_ledger_reftype_id_index');
        }

        if (Schema::hasTable('freelancer_contract_reviews')) {
            $this->dropIndexIfExists('freelancer_contract_reviews', 'freelancer_contract_reviews_created_at_index');
        }

        if (Schema::hasTable('freelancer_reviews')) {
            $this->dropIndexIfExists('freelancer_reviews', 'freelancer_reviews_created_at_index');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }
};
