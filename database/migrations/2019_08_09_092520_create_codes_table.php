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
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->string('code', 17)->nullable(false)->unique();
            $table->string('name_kk',300)->nullable(false);
            $table->string('name_ru',300)->nullable(false);
            $table->string('description_kk',1024);
            $table->string('description_ru',1024);
            $table->string('type');
            $table->timestamps();
            $table->foreign('subgroup_id')->references('id')->on('subgroups')
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
        Schema::dropIfExists('codes');
        Schema::enableForeignKeyConstraints();
    }
}
