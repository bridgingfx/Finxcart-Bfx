<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the base `users` table.
 *
 * This migration was missing from the repository: the first migration that
 * touches the table (2021_03_03_204349_add_cm_firebase_token_to_users) only
 * alters it, so a fresh `php artisan migrate` could never build the database.
 * It is dated 2021-01-01 so it runs before every later `users` alteration.
 * Columns here mirror the original 6Valley base table plus the fields the
 * User model documents as core attributes (address fields, payment card
 * fields); every column added by later dated migrations is intentionally
 * left to those migrations.
 */
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->string('image')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('street_address')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('house_no')->nullable();
            $table->string('apartment_no')->nullable();
            $table->string('payment_card_last_four')->nullable();
            $table->string('payment_card_brand')->nullable();
            $table->string('payment_card_fawry_token')->nullable();
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
        Schema::dropIfExists('users');
    }
}
