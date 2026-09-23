<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_contracts', function (Blueprint $table) {
            // Ties a contract back to the PaymentRequest that funded it, so the
            // Stripe success hook can be idempotent — a reloaded/replayed success
            // URL must not create a second contract + a second wallet credit for
            // the same payment.
            $table->char('payment_request_id', 36)->nullable()->after('freelancer_service_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_contracts', function (Blueprint $table) {
            $table->dropColumn('payment_request_id');
        });
    }
};
