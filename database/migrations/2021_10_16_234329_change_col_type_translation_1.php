<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeColTypeTranslation1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The `id` column was dropped by 2021_10_16_234037; skip when it is
        // already present (e.g. databases built before that drop existed).
        if (Schema::hasColumn('translations', 'id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            // SQLite cannot ADD a PRIMARY KEY column to an existing table,
            // so rebuild it with the auto-increment id (mirrors the MySQL
            // ALTER below). Column list matches the table as of 2021_10_16.
            Schema::create('translations_new', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('translationable_type');
                $table->unsignedBigInteger('translationable_id')->index();
                $table->string('locale')->index();
                $table->string('key')->nullable();
                $table->text('value')->nullable();
            });

            $columns = ['translationable_type', 'translationable_id', 'locale', 'key', 'value'];
            $list = implode(',', $columns);
            DB::statement("INSERT INTO translations_new ($list) SELECT $list FROM translations");
            Schema::drop('translations');
            Schema::rename('translations_new', 'translations');
            return;
        }

        Schema::table('translations', function (Blueprint $table) {
            $table->bigIncrements('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('translations', function (Blueprint $table) {
            //
        });
    }
}
