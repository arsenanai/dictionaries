<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/*
php artisan migrate --path=/database/migrations/2019_09_30_090735_add_user_to_codes.php
*/

class AddUserToCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->default(1)->nullable(false);
            $table->foreign('user_id')->references('id')->on('users')
                ->nullable(false)->change();
        });
        Schema::table('subgroups', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->default(1)->nullable(false);
            $table->foreign('user_id')->references('id')->on('users')
                ->nullable(false)->change();
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->default(1)->nullable(false);
            $table->foreign('user_id')->references('id')->on('users')
                ->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('subgroups', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
