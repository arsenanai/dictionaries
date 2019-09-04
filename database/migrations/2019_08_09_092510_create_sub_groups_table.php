<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('subgroups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id');
            $table->string('name_kk',256)->nullable(true);
            $table->string('name_ru',256);
            $table->boolean('isZKS');
            $table->timestamps();
            $table->foreign('group_id')
            ->references('id')->on('groups')
            ->nullable(false)->change();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('subgroups');
        Schema::enableForeignKeyConstraints();
    }
}
