<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImmediateHeadCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         
         if(Schema::hasTable('immediateHead_Campaigns')) return;
        Schema::create('immediateHead_Campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('immediateHead_id')->unsigned();
            $table->foreign('immediateHead_id')->references('id')->on('immediateHead')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('campaign_id')->unsigned();
            $table->foreign('campaign_id')->references('id')->on('campaign')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::drop('immediateHead_Campaigns');
    }
}
