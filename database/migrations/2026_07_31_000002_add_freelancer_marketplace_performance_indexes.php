<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_contract_reviews', function (Blueprint $table) {
            $table->index(['reviewee_type', 'reviewee_id', 'reviewer_type'], 'fc_reviews_reviewee_reviewer_index');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->index(['seller_type', 'status', 'account_status'], 'sellers_type_status_account_index');
        });

        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->index(['seller_type', 'status'], 'vendor_verifications_type_status_index');
        });

        Schema::table('freelancer_services', function (Blueprint $table) {
            $table->index(['freelancer_category_id', 'is_active'], 'freelancer_services_category_active_index');
            $table->index(['freelancer_specialization_id', 'is_active'], 'freelancer_services_specialization_active_index');
            $table->index(['seller_id', 'is_active'], 'freelancer_services_seller_active_index');
            $table->index(['seller_id', 'priority'], 'freelancer_services_seller_priority_index');
        });

        Schema::table('freelancer_portfolio_items', function (Blueprint $table) {
            $table->index(['seller_id', 'is_active'], 'freelancer_portfolio_items_seller_active_index');
            $table->index(['seller_id', 'priority'], 'freelancer_portfolio_items_seller_priority_index');
        });

        Schema::table('freelancer_contracts', function (Blueprint $table) {
            $table->index(['customer_id', 'created_at'], 'freelancer_contracts_customer_created_index');
            $table->index(['seller_id', 'created_at'], 'freelancer_contracts_seller_created_index');
            $table->index(['customer_id', 'freelancer_service_id', 'status'], 'freelancer_contracts_customer_service_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_contract_reviews', function (Blueprint $table) {
            $table->dropIndex('fc_reviews_reviewee_reviewer_index');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropIndex('sellers_type_status_account_index');
        });

        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->dropIndex('vendor_verifications_type_status_index');
        });

        Schema::table('freelancer_services', function (Blueprint $table) {
            $table->dropIndex('freelancer_services_category_active_index');
            $table->dropIndex('freelancer_services_specialization_active_index');
            $table->dropIndex('freelancer_services_seller_active_index');
            $table->dropIndex('freelancer_services_seller_priority_index');
        });

        Schema::table('freelancer_portfolio_items', function (Blueprint $table) {
            $table->dropIndex('freelancer_portfolio_items_seller_active_index');
            $table->dropIndex('freelancer_portfolio_items_seller_priority_index');
        });

        Schema::table('freelancer_contracts', function (Blueprint $table) {
            $table->dropIndex('freelancer_contracts_customer_created_index');
            $table->dropIndex('freelancer_contracts_seller_created_index');
            $table->dropIndex('freelancer_contracts_customer_service_status_index');
        });
    }
};
