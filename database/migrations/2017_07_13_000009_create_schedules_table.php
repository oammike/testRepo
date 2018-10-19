<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        

        if(Schema::hasTable('schedules')) return;
        Schema::create('schedules', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            //$table->enum('workday',['Sundays','Mondays','Tuesdays','Wednesdays','Thursdays','Fridays','Saturdays'])->nullable();
            $table->integer('workday')->unsigned()->nullable();
            $table->time('timeStart')->nullable();
            $table->time('timeEnd')->nullable();
            $table->boolean('isFlexi');


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
        Schema::drop('schedules');
    }
}
