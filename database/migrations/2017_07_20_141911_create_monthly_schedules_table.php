<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonthlySchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        

        if(Schema::hasTable('monthly_schedules')) return;
        Schema::create('monthly_schedules', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->date('productionDate');
            $table->time('timeStart')->nullable();
            $table->time('timeEnd')->nullable();
            $table->boolean('isFlexitime');
            $table->boolean('isRD');

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
        Schema::drop('monthly_schedules');
    }
}
