<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSummaryRowtablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('summary__Rowtable')) return;
        Schema::create('summary__Rowtable', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('summary_id')->unsigned();
            $table->foreign('summary_id')->references('id')->on('summary')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('rowtable_id')->unsigned();
            $table->foreign('rowtable_id')->references('id')->on('rowtable')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::drop('summary__Rowtable');
    }
}
