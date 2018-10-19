<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('team')) return;
        Schema::create('team', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('campaign_id')->unsigned();
            $table->foreign('campaign_id')->references('id')->on('campaign')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('immediateHead_Campaigns_id')->unsigned();
            $table->foreign('immediateHead_Campaigns_id')->references('id')->on('immediateHead_Campaigns')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('floor_id')->unsigned();
            $table->foreign('floor_id')->references('id')->on('floor')->onUpdate('cascade')->onDelete('cascade');
            


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
        Schema::drop('team');
    }
}
