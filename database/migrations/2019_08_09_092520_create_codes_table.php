<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->string('code', 17)->nullable(false)->unique();
            $table->string('name_kk',256)->nullable(false);
            $table->string('name_ru',256)->nullable(false);
            $table->string('description_kk',256);
            $table->string('description_ru',256);
            $table->boolean('isZKS');
            $table->timestamps();
            $table->foreign('group_id')->references('id')->on('groups')
            ->nullable(false)->change();
            $table->foreign('subgroup_id')
            ->references('id')->on('subgroups');
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
        Schema::dropIfExists('codes');
        Schema::enableForeignKeyConstraints();
    }
}
