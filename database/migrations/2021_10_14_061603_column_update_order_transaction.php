<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColumnUpdateOrderTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Split into two statements: SQLite cannot combine dropColumn with
        // other operations in a single table modification.
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('order_transactions', function (Blueprint $table) {
            $table->string('transaction_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            //
        });
    }
}
