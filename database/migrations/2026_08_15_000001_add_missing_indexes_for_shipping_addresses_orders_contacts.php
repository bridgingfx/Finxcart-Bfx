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
        // shipping_addresses: zero indexes beyond PRIMARY, despite backing the
        // customer address book and being queried by customer_id on essentially
        // every checkout page load (checkout_details, checkout_payment) and every
        // address management page.
        if (Schema::hasTable('shipping_addresses')) {
            Schema::table('shipping_addresses', function (Blueprint $table) {
                $table->index(['customer_id', 'is_guest'], 'shipping_addresses_customer_guest_index');
            });
        }

        // orders: checked/seller_is have no supporting index. Hit by an unconditional
        // "mark as viewed" UPDATE on every Order List page load (admin + vendor) and
        // polled every 5s by the vendor dashboard's real-time activity widget.
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['seller_id', 'seller_is', 'checked'], 'orders_seller_sellerid_checked_index');
            });
        }

        // contacts: zero indexes beyond PRIMARY. Filtered by `seen` and sorted by
        // created_at on the admin header/notification poll.
        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->index(['seen', 'created_at'], 'contacts_seen_created_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shipping_addresses')) {
            $this->dropIndexIfExists('shipping_addresses', 'shipping_addresses_customer_guest_index');
        }

        if (Schema::hasTable('orders')) {
            $this->dropIndexIfExists('orders', 'orders_seller_sellerid_checked_index');
        }

        if (Schema::hasTable('contacts')) {
            $this->dropIndexIfExists('contacts', 'contacts_seen_created_index');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }
};
