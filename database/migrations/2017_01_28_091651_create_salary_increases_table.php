<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryIncreasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('salaryIncreases')) return;
         Schema::create('salaryIncreases', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('upperLimit',5,2);
            $table->decimal('lowerLimit',5,2);
            $table->integer('percentage')->unsigned();
            
            $table->integer('evalSetting_id')->unsigned();
            $table->foreign('evalSetting_id')->references('id')->on('evalSetting')->onUpdate('cascade')->onDelete('cascade');


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
        Schema::drop('salaryIncreases');
    }
}
