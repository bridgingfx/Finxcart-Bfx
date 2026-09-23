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
        // chattings: user_id/seller_id/admin_id/delivery_man_id already had single-column
        // indexes, but `sent_by_customer` and `seen_by_customer` did not. The customer-side
        // inbox (Web\ChattingController::getChatList, via the new
        // ChattingRepository::countUnseenForCustomerGroupedBy) now runs
        // where(user_id = ?)->where(sent_by_customer = 0)->where(seen_by_customer = 0)
        // ->groupBy(<thread column>) once per page load (batched to replace what used to
        // be one COUNT query per chat thread). No FK constraint exists on this table
        // (verified via information_schema), so a plain index/drop is safe.
        if (Schema::hasTable('chattings')) {
            Schema::table('chattings', function (Blueprint $table) {
                $table->index(['user_id', 'sent_by_customer', 'seen_by_customer'], 'chattings_user_unseen_lookup_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chattings')) {
            $this->dropIndexIfExists('chattings', 'chattings_user_unseen_lookup_index');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }
};
