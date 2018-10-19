<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHolidaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('holidays')) return;
        Schema::create('holidays', function (Blueprint $table) {
            $table->increments('id');

            $table->date('holidate');
            $table->string('name');

            $table->integer('holidayType_id')->unsigned();
            $table->foreign('holidayType_id')->references('id')->on('holiday_types')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::drop('holidays');
    }
}
