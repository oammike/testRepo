<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImmediateHeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('immediateHead')) return;
        Schema::create('immediateHead', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('employeeNumber');
            $table->string('firstname');
            $table->string('lastname');

            // $table->integer('campaign_id')->unsigned();
            // $table->foreign('campaign_id')->references('id')->on('campaign')->onUpdate('cascade');

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
        Schema::drop('immediateHead');
    }
}
