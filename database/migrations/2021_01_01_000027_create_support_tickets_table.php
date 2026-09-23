<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the base `support_tickets` table.
 *
 * This migration was missing from the repository: the migration history only
 * contains alterations of this table, so a fresh `php artisan migrate` could
 * never build the database. It is dated 2021-01-01 so it runs before every
 * later `support_tickets` alteration. Columns mirror the corresponding Eloquent model;
 * columns introduced by later dated migrations are left to those migrations.
 */
class CreateSupportTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('type')->nullable();
            $table->string('priority')->nullable();
            $table->string('description')->nullable();
            $table->string('reply')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('support_tickets');
    }
}
