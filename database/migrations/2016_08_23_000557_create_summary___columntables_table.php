<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSummaryColumntablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
         if(Schema::hasTable('summary__Columntable')) return;
         Schema::create('summary__Columntable', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('summary_id')->unsigned();
            $table->foreign('summary_id')->references('id')->on('summary')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('columntable_id')->unsigned();
            $table->foreign('columntable_id')->references('id')->on('columntable')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::drop('summary__Columntable');
    }
}
