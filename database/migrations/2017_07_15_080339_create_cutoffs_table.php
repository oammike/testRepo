<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCutoffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('cutoffs')) return;
        Schema::create('cutoffs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('first',false,true)->unsigned();
            $table->integer('second',false,true)->unsigned();
            $table->integer('paydayInterval',false,true)->length(2);
            $table->integer('month13th',false,true)->length(2);
            $table->integer('day13th',false,true)->length(2);
            $table->integer('synchMonth1',false,true)->length(2);
            $table->integer('synchDate1',false,true)->length(2);
            $table->integer('synchMonth2',false,true)->length(2);
            $table->integer('synchDate2',false,true)->length(2);
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
        Schema::drop('cutoffs');
    }
}
