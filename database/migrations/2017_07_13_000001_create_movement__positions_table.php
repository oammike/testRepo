<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('movement_positions')) return;
         Schema::create('movement_positions', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('movement_id')->unsigned();
            $table->foreign('movement_id')->references('id')->on('movement')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('position_id_old')->unsigned();
            $table->foreign('position_id_old')->references('id')->on('positions')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('position_id_new')->unsigned();
            $table->foreign('position_id_new')->references('id')->on('positions')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::drop('movement_positions');
    }
}
