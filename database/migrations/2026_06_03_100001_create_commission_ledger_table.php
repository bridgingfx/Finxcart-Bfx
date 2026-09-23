<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commission_ledger')) {
            return;
        }

        Schema::create('commission_ledger', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('reference_type', 80)->default('order')->comment('order | crm_webhook | manual');
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->decimal('gross_amount', 20, 4)->comment('Full transaction value before deductions');
            $table->decimal('company_share_amount', 20, 4)->comment('Platform share amount');
            $table->decimal('service_charge_amount', 20, 4)->default(0)->comment('Variable processing/service fee');
            $table->decimal('net_vendor_payout', 20, 4)->comment('Gross minus platform share and service charge');
            $table->char('currency', 3)->default('USD');
            $table->decimal('platform_rate', 5, 2)->default(15.00)->comment('Commission rate percentage used');
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_ledger');
    }
};
