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
        // order_transactions: zero indexes beyond PRIMARY. Filtered/searched by these
        // columns on the admin transaction list (every page load), seller transaction
        // tab, and dashboard date-range aggregates.
        if (Schema::hasTable('order_transactions')) {
            Schema::table('order_transactions', function (Blueprint $table) {
                $table->index('seller_id', 'order_transactions_seller_id_index');
                $table->index('customer_id', 'order_transactions_customer_id_index');
                $table->index('order_id', 'order_transactions_order_id_index');
                $table->index('status', 'order_transactions_status_index');
                $table->index('created_at', 'order_transactions_created_at_index');
            });
        }

        // notifications: zero indexes beyond PRIMARY, despite being queried with
        // sent_to + created_at range filters on every vendor/freelancer page load.
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['sent_to', 'created_at'], 'notifications_sent_to_created_index');
            });
        }

        // notification_seens: zero indexes beyond PRIMARY, despite backing the
        // whereDoesntHave/with('notificationSeenBy') correlated lookups above.
        if (Schema::hasTable('notification_seens')) {
            Schema::table('notification_seens', function (Blueprint $table) {
                $table->index(['notification_id', 'user_id'], 'notification_seens_notification_user_index');
                $table->index(['notification_id', 'seller_id'], 'notification_seens_notification_seller_index');
            });
        }

        // shop_followers: zero indexes beyond PRIMARY, despite the "am I following
        // this shop" check filtering by user_id + shop_id on shop-page views.
        if (Schema::hasTable('shop_followers')) {
            Schema::table('shop_followers', function (Blueprint $table) {
                $table->index(['user_id', 'shop_id'], 'shop_followers_user_shop_index');
            });
        }

        // users (the real customer-record table — App\Models\Customer is dead code):
        // is_active is filtered on the admin customer list with no supporting index.
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('is_active', 'users_is_active_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_transactions')) {
            $this->dropIndexIfExists('order_transactions', 'order_transactions_seller_id_index');
            $this->dropIndexIfExists('order_transactions', 'order_transactions_customer_id_index');
            $this->dropIndexIfExists('order_transactions', 'order_transactions_order_id_index');
            $this->dropIndexIfExists('order_transactions', 'order_transactions_status_index');
            $this->dropIndexIfExists('order_transactions', 'order_transactions_created_at_index');
        }

        if (Schema::hasTable('notifications')) {
            $this->dropIndexIfExists('notifications', 'notifications_sent_to_created_index');
        }

        if (Schema::hasTable('notification_seens')) {
            $this->dropIndexIfExists('notification_seens', 'notification_seens_notification_user_index');
            $this->dropIndexIfExists('notification_seens', 'notification_seens_notification_seller_index');
        }

        if (Schema::hasTable('shop_followers')) {
            $this->dropIndexIfExists('shop_followers', 'shop_followers_user_shop_index');
        }

        if (Schema::hasTable('users')) {
            $this->dropIndexIfExists('users', 'users_is_active_index');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }
};
