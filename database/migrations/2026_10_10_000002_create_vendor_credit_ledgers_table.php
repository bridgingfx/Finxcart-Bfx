<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Append-only ledger of vendor promotional-credit movements.
     * Every grant, purchase, spend, refund or expiry writes one row with the
     * running balance, so a vendor's balance is always auditable.
     */
    public function up(): void
    {
        if (Schema::hasTable('vendor_credit_ledgers')) {
            return;
        }

        Schema::create('vendor_credit_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->index();
            $table->string('type', 20)->comment('grant | purchase | spend | refund | expire');
            $table->integer('credits')->comment('Signed delta: positive for grants/purchases, negative for spends');
            $table->integer('balance_after')->comment('Running credit balance after this entry');
            $table->string('related_type', 120)->nullable()->comment('e.g. promotion activity or package slug/class');
            $table->unsignedBigInteger('related_id')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'created_at']);
            $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_credit_ledgers');
    }
};
