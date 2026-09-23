<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Freelancer services
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('freelancer_services')) {
            if (!Schema::hasColumn('freelancer_services', 'seller_id')) {
                Schema::table('freelancer_services', function (Blueprint $table): void {
                    $table->foreignId('seller_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('sellers')
                        ->cascadeOnDelete();
                });
            }

            /*
             * The previous migration may already have removed this column.
             * Only modify it when it still exists.
             */
            if (Schema::hasColumn('freelancer_services', 'freelancer_profile_id')) {
                Schema::table('freelancer_services', function (Blueprint $table): void {
                    $table->unsignedBigInteger('freelancer_profile_id')
                        ->nullable()
                        ->change();
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Freelancer portfolio items
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('freelancer_portfolio_items')) {
            if (!Schema::hasColumn('freelancer_portfolio_items', 'seller_id')) {
                Schema::table('freelancer_portfolio_items', function (Blueprint $table): void {
                    $table->foreignId('seller_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('sellers')
                        ->cascadeOnDelete();
                });
            }

            if (
                Schema::hasColumn(
                    'freelancer_portfolio_items',
                    'freelancer_profile_id'
                )
            ) {
                Schema::table(
                    'freelancer_portfolio_items',
                    function (Blueprint $table): void {
                        $table->unsignedBigInteger('freelancer_profile_id')
                            ->nullable()
                            ->change();
                    }
                );
            }
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('freelancer_services') &&
            Schema::hasColumn('freelancer_services', 'seller_id')
        ) {
            Schema::table('freelancer_services', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('seller_id');
            });
        }

        if (
            Schema::hasTable('freelancer_portfolio_items') &&
            Schema::hasColumn('freelancer_portfolio_items', 'seller_id')
        ) {
            Schema::table(
                'freelancer_portfolio_items',
                function (Blueprint $table): void {
                    $table->dropConstrainedForeignId('seller_id');
                }
            );
        }
    }
};
