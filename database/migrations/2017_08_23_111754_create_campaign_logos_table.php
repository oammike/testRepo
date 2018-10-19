<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignLogosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('campaign_logos')) return;
        Schema::create('campaign_logos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('campaign_id')->unsigned();
            $table->foreign('campaign_id')->references('id')->on('campaign')->onUpdate('cascade')->onDelete('cascade');

            $table->string('filename');

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
        Schema::drop('campaign_logos');
    }
}
