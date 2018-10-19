<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementImmediateHeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('movement_immediateHead')) return;
         Schema::create('movement_immediateHead', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('movement_id')->unsigned();
            $table->foreign('movement_id')->references('id')->on('movement')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('imHeadCampID_old')->unsigned();
            $table->foreign('imHeadCampID_old')->references('id')->on('immediateHead_Campaigns')->onUpdate('cascade')->onDelete('cascade');
            //$table->foreign('immediateHead_id_old')->references('id')->on('immediateHead_Campaigns')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('imHeadCampID_new')->unsigned();
            $table->foreign('imHeadCampID_new')->references('id')->on('immediateHead_Campaigns')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('oldFloor')->unsigned();
            $table->foreign('oldFloor')->references('id')->on('floor')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('newFloor')->unsigned();
            $table->foreign('newFloor')->references('id')->on('floor')->onUpdate('cascade')->onDelete('cascade');

           

           
            

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
        Schema::drop('movement_immediateHead');
    }
}
