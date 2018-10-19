<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('movement_statuses')) return;
         Schema::create('movement_statuses', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('movement_id')->unsigned();
            $table->foreign('movement_id')->references('id')->on('movement')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('status_id_old')->unsigned();
            $table->foreign('status_id_old')->references('id')->on('statuses')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('status_id_new')->unsigned();
            $table->foreign('status_id_new')->references('id')->on('statuses')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::drop('movement_statuses');
    }
}
